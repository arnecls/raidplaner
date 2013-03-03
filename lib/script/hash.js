var s_Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

// -----------------------------------------------------------------------------

function finalHash( a_Key, a_Hash )
{
    return CryptoJS.SHA256(a_Key + a_Hash).toString();
}

// -----------------------------------------------------------------------------

function hash( a_Key, a_HashType, a_Password, a_Salt, a_OnUpdate, a_OnDone ) 
{
    var HashedPassword = "";
            
    switch ( a_HashType )
    {
    case "cleartext":
        a_OnDone( a_Password );
        return;
    
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
    
    a_OnDone( finalHash(a_Key, HashedPassword) );
}

// -----------------------------------------------------------------------------

function GetPasswordStrength( a_Password )
{
    var color   = "#ccc";
    var quality = 0;
        
    if ( a_Password.length > 0 )
    {
        var charTypes = new Array( 
            { used : 0, min: 32,  max: 48  },  // special chars 1
            { used : 0, min: 48,  max: 58  },  // number
            { used : 0, min: 58,  max: 65  },  // special chars 2
            { used : 0, min: 65,  max: 91  },  // A-Z
            { used : 0, min: 91,  max: 97  },  // special chars 2
            { used : 0, min: 97,  max: 123 },  // a-z
            { used : 0, min: 123, max: 127 }); // special chars 3
            
        var colors = new Array(
            { r: 255, g: 0,   b: 0 },
            { r: 255, g: 255, b: 0 },
            { r: 0,   g: 255, b: 0 });
            
        // Analyze charset
    
        for ( i=0; i < a_Password.length; ++i )
        {
            var charCode = a_Password.charCodeAt(i);
            for ( ctIdx=0; ctIdx < charTypes.length; ++ctIdx )
            {
                if ( (charCode >= charTypes[ctIdx].min) && (charCode < charTypes[ctIdx].max) )
                {
                    ++charTypes[ctIdx].used;
                    break;
                }
            }
        }
        
        var variantBase = 0;
        var asciiChars  = 0;
        
        for ( ctIdx=0; ctIdx < charTypes.length; ++ctIdx )
        {
            if ( charTypes[ctIdx].used > 0 )
            {
                asciiChars  += charTypes[ctIdx].used;
                variantBase += charTypes[ctIdx].max - charTypes[ctIdx].min;
            }
        }
        
        if ( asciiChars < a_Password.length )
            variantBase += 32;
        
        // Choose correct color and progress
        
        quality = Math.min(1.0, Math.pow(variantBase, a_Password.length/10.0) / 128.0 );
        
        color = "#";
        var segmentSize  = 1.0 / (colors.length-1);
        var baseColorIdx = Math.min( parseInt(quality / segmentSize), colors.length-2 );
        var scale        = (quality - (segmentSize * baseColorIdx)) / segmentSize;
        
        var minColor = colors[baseColorIdx];
        var maxColor = colors[baseColorIdx+1];
        
        var r = parseInt(minColor.r * (1-scale) + maxColor.r * scale);
        var g = parseInt(minColor.g * (1-scale) + maxColor.g * scale);
        var b = parseInt(minColor.b * (1-scale) + maxColor.b * scale);
        
        color += ((r<16) ? "0" : "") + r.toString(16);
        color += ((g<16) ? "0" : "") + g.toString(16);
        color += ((b<16) ? "0" : "") + b.toString(16);
    }
    
    return {
        color   : color,
        quality : quality
    };
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
    var config = parts[0];
    var salt   = parts[1];    
    
    // TODO: Inner workings of PHP crypt with EXT_DES.
    //       Find javascript DES based hasher. CryptoJS.DES.encrypt is slow and
    //       most likely not the correct function.
    
    var preHash = CryptoJS.SHA512(salt + a_Password).toString();
    var hash = CryptoJS.DES.encrypt(preHash, config);
        
    alert("EQDKP password has been hashed using crypt() with CRYPT_EXT_DES."+
          "This is not yet supported due to missing details about the crypt() implementation.\n"+
          "You can set \"USE_CLEARTEXT_PASSWORDS\" to \"true\" in the config to work around this problem.");
    
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