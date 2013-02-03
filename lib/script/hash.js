var s_Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

// -----------------------------------------------------------------------------

function finalHash( a_Key, a_Hash )
{
    return CryptoJS.SHA256(a_Key + a_Hash);
}

// -----------------------------------------------------------------------------

function hash( a_Key, a_HashType, a_Password, a_Salt, a_OnUpdate, a_OnDone ) 
{
    var HashedPassword = "";
            
    switch ( a_HashType )
    {
    case "native_sha256s":
        HashedPassword = hash_native_sha256s(a_Password, a_Salt);
        break;
        
    case "phpbb3_md5r":
        hash_phpbb3_md5r(a_Password, a_Salt, a_OnUpdate, function(HashedPassword) {
            a_OnDone( finalHash(a_Key, HashedPassword) );
        });
        return;
        
    case "phpbb3_md5":
    case "eqdkp_md5":
        HashedPassword = hash_generic_md5(a_Password);
        break;
        
    case "eqdkp_sha512s":
        HashedPassword = hash_eqdkp_sha512s(a_Password, a_Salt);
        break;
        
    case "eqdkp_sha512sb":
        hash_eqdkp_sha512sb(a_Password, a_Salt, a_OnUpdate, function(HashedPassword) {
            a_OnDone( finalHash(a_Key, HashedPassword) );
        });
        return;
        
    case "eqdkp_sha512sd":
        hash_eqdkp_sha512sd(a_Password, a_Salt, a_OnUpdate, function(HashedPassword) {
            a_OnDone( finalHash(a_Key, HashedPassword) );
        });
        return;
        
    case "eqdkp_sha512r":
        hash_eqdkp_sha512r(a_Password, a_Salt, a_OnUpdate, function(HashedPassword) {
            a_OnDone( finalHash(a_Key, HashedPassword) );
        });
        return;
        
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
    
    a_OnUpdate(100);
    a_OnDone( finalHash(a_Key, HashedPassword) );
}

// -----------------------------------------------------------------------------

function encode64( a_WordArray )
{
    // Convert the words of the cryptjs hash to a byte stream in the correct
    // order.
    
    var bytesArray = new Array();
    for ( var i = 0; i < a_WordArray.length; ++i )
	{
        bytesArray.push( (a_WordArray[i] >> 24) & 0xFF );
        bytesArray.push( (a_WordArray[i] >> 16) & 0xFF );
        bytesArray.push( (a_WordArray[i] >> 8) & 0xFF );
        bytesArray.push( a_WordArray[i] & 0xFF );
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
       
       encoded += s_Itoa64[(value >>> 18) & 0x3f];
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

function hash_phpbb3_md5r(a_Password, a_Salt, a_OnUpdate, a_OnDone)
{
    var parts   = a_Salt.split(":");
    var countB2 = parseInt(parts[0], 10);
    var count   = 1 << countB2;
    var salt    = parts[1];
    
    // The original implementation uses md5(..., true) i.e. we're working with the
    // the bytes result and not with the hex representation of it.
    // This requires progressive hashing in CryptoJS.
    
    var hash = CryptoJS.MD5(salt + a_Password);
    var roundHash = CryptoJS.algo.MD5.create();
    var round = 0;
    var lastUpdate = 0;
    
    var loopFunc = function() 
    {
        while (round < count)
        {
            roundHash.update( hash );
            roundHash.update( a_Password );
            hash = roundHash.finalize();
            roundHash.reset();
            ++round;
            
            var progress = parseInt((round/count)*100);
            if ( progress != lastUpdate )
            {
                lastUpdate = progress;
                a_OnUpdate( progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        a_OnUpdate(100);
        a_OnDone("$H$" + s_Itoa64.charAt(countB2) + salt + encode64(hash.words));
    };
    
    window.setTimeout(loopFunc,0);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512s(a_Password, a_Salt)
{
    return CryptoJS.SHA512(a_Salt + a_Password);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512sb(a_Password, a_Salt, a_OnUpdate, a_OnDone)
{
    var bcrypt = new bCrypt();
	var parts  = a_Salt.split(":");
    var config = parts[0];
    var salt   = parts[1];  
    
    var preHash = CryptoJS.SHA512(salt + a_Password).toString();
    
    bcrypt.hashpw( preHash, config, a_OnUpdate, function(bfHash) {
        a_OnUpdate(100);
        a_OnDone(bfHash + ":" + salt);
    });
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512sd(a_Password, a_Salt, a_OnUpdate, a_OnDone)
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
    
    a_OnUpdate(100);
    a_OnDone("_" + key + hash + ":" + salt);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512r(a_Password, a_Salt, a_OnUpdate, a_OnDone)
{
    var parts   = a_Salt.split(":");
    var countB2 = parseInt(parts[0], 10);
    var count   = 1 << countB2;
    var salt    = parts[1];
    var salt2   = parts[2];
    
    var preHash = CryptoJS.SHA512(salt + a_Password).toString();
    var hash = CryptoJS.SHA512(salt2 + preHash);
    var roundHash = CryptoJS.algo.SHA512.create();
    var round = 0;
    var lastUpdate = 0;
    
    var loopFunc = function() 
    {
        while ( round < count )
        {
            roundHash.update( hash );
            roundHash.update( preHash );
            hash = roundHash.finalize();
            roundHash.reset();
            ++round;
            
            var progress = parseInt((round/count)*100);
            if ( progress != lastUpdate )
            {
                lastUpdate = progress;
                a_OnUpdate( progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        a_OnUpdate(100);
        a_OnDone("$S$" + s_Itoa64.charAt(countB2) + salt2 + encode64(hash.words) + ":" + salt);
    };
    
    window.setTimeout(loopFunc,0);
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