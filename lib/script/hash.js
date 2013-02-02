var s_Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

// -----------------------------------------------------------------------------

function hash( a_Key, a_HashType, a_Password, a_Salt ) 
{
    var HashedPassword = "";
    
    //alert(a_HashType);
    
    switch ( a_HashType )
    {
    case "native_sha256s":
        HashedPassword = hash_native_sha256s(a_Password, a_Salt);
        break;
        
    case "phpbb3_md5r":
        HashedPassword = hash_phpbb3_md5r(a_Password, a_Salt);
        break;
        
    case "phpbb3_md5":
    case "eqdkp_md5":
        HashedPassword = hash_generic_md5(a_Password);
        break;
        
    case "eqdkp_sha512s":
        HashedPassword = hash_eqdkp_sha512s(a_Password, a_Salt);
        break;
        
    case "eqdkp_sha512sb":
        HashedPassword = hash_eqdkp_sha512sb(a_Password, a_Salt);
        break;
        
    case "eqdkp_sha512sd":
        HashedPassword = hash_eqdkp_sha512sd(a_Password, a_Salt);
        break;
        
    case "eqdkp_sha512r":
        HashedPassword = hash_eqdkp_sha512r(a_Password, a_Salt);
        break;
        
    case "mybb_md5s":
        HashedPassword = hash_mybb_md5s(a_Password, a_Salt);
        break;
        
    case "smf_sha1s":
        HashedPassword = hash_smf_sha1s(a_Password, a_Salt);
        break;
        
    case "vb3_md5s":
        HashedPassword = hash_vb3_md5s(a_Password, a_Salt);
        break;
        
    default:
        break;
    }
    
    //alert( HashedPassword );
    
    return CryptoJS.SHA256( a_Key + HashedPassword );
}

// -----------------------------------------------------------------------------

function encode64( a_WordArray )
{
    // Convert the words of the cryptjs hash to a byte stream in the correct
    // order.
    
    var bytesArray = new Array();
    for ( var i = 0; i < a_WordArray.length; ++i )
	{
	   if (typeof(a_WordArray[i]) == "object")
	   {
	       bytesArray.push( (a_WordArray[i].high >> 24) & 0xFF );
	       bytesArray.push( (a_WordArray[i].high >> 16) & 0xFF );
	       bytesArray.push( (a_WordArray[i].high >> 8) & 0xFF );
	       bytesArray.push( a_WordArray[i].high & 0xFF );
	       	       
	       bytesArray.push( (a_WordArray[i].low >> 24) & 0xFF );
	       bytesArray.push( (a_WordArray[i].low >> 16) & 0xFF );
	       bytesArray.push( (a_WordArray[i].low >> 8) & 0xFF );
	       bytesArray.push( a_WordArray[i].low & 0xFF );
	   }
	   else
	   {
    	   bytesArray.push( (a_WordArray[i] >> 24) & 0xFF );
    	   bytesArray.push( (a_WordArray[i] >> 16) & 0xFF );
    	   bytesArray.push( (a_WordArray[i] >> 8) & 0xFF );
    	   bytesArray.push( a_WordArray[i] & 0xFF );
	   }
    }
	
	// Process the "string".
	// This code has been ported 1:1 to JS from phpbb3's PHP implementation.
    
	var encoded = "";
	
    for ( var i = 0; i < bytesArray.length; )
	{
       var value = bytesArray[i++];
       encoded += s_Itoa64[value & 0x3f];
    
       if (i < bytesArray.length)
       {
           value |= bytesArray[i] << 8;
       }
       
       encoded += s_Itoa64[(value >> 6) & 0x3f];
       
       if (i++ >= bytesArray.length)
       {
           break;
       }

       if (i < bytesArray.length)
       {
           value |= bytesArray[i] << 16;
       }
       
       encoded += s_Itoa64[(value >> 12) & 0x3f];
       
       if (i++ >= bytesArray.length)
       {
           break;
       }
       
       encoded += s_Itoa64[(value >> 18) & 0x3f];
    }
    
    return encoded;
}

// -----------------------------------------------------------------------------

function hash_native_sha256s(a_Password, a_Salt)
{
    return CryptoJS.SHA256(CryptoJS.SHA1(a_Password) + a_Salt);
}

// -----------------------------------------------------------------------------

function hash_generic_md5(a_Password, a_Salt)
{
    return CryptoJS.MD5(a_Password);
}

// -----------------------------------------------------------------------------

function hash_phpbb3_md5r(a_Password, a_Salt)
{
    var parts   = a_Salt.split(":");
    var countB2 = parseInt(parts[0], 10);
    var count   = 1 << countB2;
    var salt    = parts[1];
    
    // The original implementation uses md5(…,true) i.e. we're working with the
    // the bytes result and not with the hex representation of it.
    // This requires progressive hashing in CryptoJS.
    
    var hash = CryptoJS.MD5(salt + a_Password);
    
    do {
        var roundHash = CryptoJS.algo.MD5.create();
        roundHash.update( hash );
        roundHash.update( a_Password );
        roundHash.finalize();
    
        hash = roundHash._hash;
    } while (--count);
    
    return "$H$" + s_Itoa64.charAt(countB2) + salt + encode64(hash.words);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512s(a_Password, a_Salt)
{
    return CryptoJS.SHA512(a_Salt + a_Password);
}

// -----------------------------------------------------------------------------

function done( a_Pass )
{
    alert(a_Pass);
}

function hash_eqdkp_sha512sb(a_Password, a_Salt)
{
    var bcrypt = new bCrypt();
	var parts  = a_Salt.split(":");
    var config = parts[0];
    var salt   = parts[1];  
    
    var preHash = CryptoJS.SHA512(salt + a_Password).toString();
    var bfHash = bcrypt.hashpw( preHash, config );
	    
    return bfHash + ":" + salt;
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512sd(a_Password, a_Salt)
{
    var parts  = a_Salt.split(":");
    var rounds = parts[0];
    var key    = parts[1];
    var salt   = parts[2];    
    
    // TODO: Inner workings of PHP crypt with EXT_DES.
    //       Find javascript DES based hasher. CryptoJS.DES.encrypt is slow and
    //       most likely not the correct function.
    
    var preHash = CryptoJS.SHA512(salt + a_Password).toString();
    var hash = CryptoJS.DES.encrypt(preHash, key);
        
    alert("EQDKP password has been hashed using crypt() with CRYPT_EXT_DES."+
          "This is not yet supported due to missing details about the crypt() implementation.\n");
    
    return "_" + key + hash + ":" + salt;
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512r(a_Password, a_Salt)
{
    var parts   = a_Salt.split(":");
    var countB2 = parseInt(parts[0], 10);
    var count   = 1 << countB2;
    var salt    = parts[1];
    var salt2   = parts[2];
    
    var preHash = CryptoJS.SHA512(salt + a_Password).toString();
    var hash = CryptoJS.SHA512(salt2 + preHash);
    
    alert("EQDKP password has been hashed with sha512 rounds.\n"+
          "This is not yet supported due to a possible bug in CryptoJS.");
    
    // TODO: Progressive SHA512 hashing produces qwords, not dwords as regular
    //       hashing does. Furthermore this is *slow* 
    
    /*do {
        var roundHash = CryptoJS.algo.SHA512.create();
        roundHash.update( hash );
        roundHash.update( preHash );
        roundHash.finalize();
        
        hash = roundHash._hash;
    } while (--count);*/
        
    return "$S$" + s_Itoa64.charAt(countB2) + salt2 + encode64(hash.words) + ":" + salt;
}

// -----------------------------------------------------------------------------

function hash_mybb_md5s(a_Password, a_Salt)
{
    return CryptoJS.MD5(CryptoJS.MD5(a_Salt) + CryptoJS.MD5(a_Password));
}

// -----------------------------------------------------------------------------

function hash_smf_sha1s(a_Password, a_Salt)
{
    return CryptoJS.SHA1(a_Salt + a_Password);
}

// -----------------------------------------------------------------------------

function hash_vb3_md5s(a_Password, a_Salt)
{
    return CryptoJS.MD5(CryptoJS.MD5(a_Password) + a_Salt);
}