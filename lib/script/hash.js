var s_Itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

// -----------------------------------------------------------------------------

function finalHash( aKey, aHash )
{
    return CryptoJS.SHA256(aKey + aHash).toString();
}

// -----------------------------------------------------------------------------

function hash( aKey, aHashType, aPassword, aSalt, aOnUpdate, aOnDone ) 
{
    var HashedPassword = "";
            
    switch ( aHashType )
    {
    case "cleartext":
        aOnDone( aPassword );
        return;
    
    case "native_sha256s":
        HashedPassword = hash_native_sha256s(aPassword, aSalt);
        break;
        
    case "phpbb3_md5r":
        hash_phpbb3_md5r(aPassword, aSalt, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "phpbb3_md5":
    case "eqdkp_md5":
        HashedPassword = hash_generic_md5(aPassword);
        break;
        
    case "eqdkp_sha512s":
        HashedPassword = hash_eqdkp_sha512s(aPassword, aSalt);
        break;
        
    case "eqdkp_sha512sb":
        hash_eqdkp_sha512sb(aPassword, aSalt, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "eqdkp_sha512sd":
        hash_eqdkp_sha512sd(aPassword, aSalt, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "eqdkp_sha512r":
        hash_eqdkp_sha512r(aPassword, aSalt, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "mybb_md5s":
        HashedPassword = hash_mybb_md5s(aPassword, aSalt);
        break;
        
    case "smf_sha1s":
        HashedPassword = hash_smf_sha1s(aPassword, aSalt);
        break;
        
    case "vb3_md5s":
        HashedPassword = hash_vb3_md5s(aPassword, aSalt);
        break;
        
    case "jml_md5s":
        HashedPassword = hash_jml_md5s(aPassword, aSalt);
        break;
        
    case "vanillamd5r":
        hash_vanillamd5r(aPassword, aSalt, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    default:
        break;
    }
    
    aOnDone( finalHash(aKey, HashedPassword) );
}

// -----------------------------------------------------------------------------

function GetPasswordStrength( aPassword )
{
    var color   = "#ccc";
    var quality = 0;
        
    if ( aPassword.length > 0 )
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
    
        for ( i=0; i < aPassword.length; ++i )
        {
            var charCode = aPassword.charCodeAt(i);
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
        
        if ( asciiChars < aPassword.length )
            variantBase += 32;
        
        // Choose correct color and progress
        
        quality = Math.min(1.0, Math.pow(variantBase, aPassword.length/10.0) / 128.0 );
        
        color = "#";
        var segmentSize  = 1.0 / (colors.length-1);
        var baseColorIdx = Math.min( parseInt(quality / segmentSize, 10), colors.length-2 );
        var scale        = (quality - (segmentSize * baseColorIdx)) / segmentSize;
        
        var minColor = colors[baseColorIdx];
        var maxColor = colors[baseColorIdx+1];
        
        var r = parseInt(minColor.r * (1-scale) + maxColor.r * scale, 10);
        var g = parseInt(minColor.g * (1-scale) + maxColor.g * scale, 10);
        var b = parseInt(minColor.b * (1-scale) + maxColor.b * scale, 10);
        
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

function encode64( aWordArray )
{
    // Convert the words of the cryptjs hash to a byte stream in the correct
    // order.
    
    var bytesArray = [];
    for ( var i = 0; i < aWordArray.length; ++i )
    {
        bytesArray.push( (aWordArray[i] >> 24) & 0xFF );
        bytesArray.push( (aWordArray[i] >> 16) & 0xFF );
        bytesArray.push( (aWordArray[i] >> 8) & 0xFF );
        bytesArray.push( aWordArray[i] & 0xFF );
    }
    
    // Process the "string".
    // This code has been ported 1:1 to JS from phpbb3's PHP implementation.
    
    var encoded = "";
    
    for ( i = 0; i < bytesArray.length; )
    {
       var value = bytesArray[i++];
       encoded += s_Itoa64[value & 0x3f];
    
       if (i < bytesArray.length)
           value |= bytesArray[i] << 8;
       
       encoded += s_Itoa64[(value >> 6) & 0x3f];
       
       if (i++ >= bytesArray.length)
           break;
       
       if (i < bytesArray.length)
           value |= bytesArray[i] << 16;
       
       encoded += s_Itoa64[(value >> 12) & 0x3f];
       
       if (i++ >= bytesArray.length)
           break;
       
       encoded += s_Itoa64[(value >>> 18) & 0x3f];
    }
    
    return encoded;
}

// -----------------------------------------------------------------------------

function hash_native_sha256s(aPassword, aSalt)
{
    return CryptoJS.SHA256(CryptoJS.SHA1(aPassword) + aSalt);
}

// -----------------------------------------------------------------------------

function hash_generic_md5(aPassword, aSalt)
{
    return CryptoJS.MD5(aPassword);
}

// -----------------------------------------------------------------------------

function hash_phpbb3_md5r(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var parts   = aSalt.split(":");
    var countB2 = parseInt(parts[0], 10);
    var count   = 1 << countB2;
    var salt    = parts[1];
    
    // The original implementation uses md5(..., true) i.e. we're working with the
    // the bytes result and not with the hex representation of it.
    // This requires progressive hashing in CryptoJS.
    
    var hash = CryptoJS.MD5(salt + aPassword);
    var roundHash = CryptoJS.algo.MD5.create();
    var round = 0;
    var lastUpdate = 0;
    
    var loopFunc = function() 
    {
        while (round < count)
        {
            roundHash.update( hash );
            roundHash.update( aPassword );
            hash = roundHash.finalize();
            roundHash.reset();
            ++round;
            
            var progress = parseInt((round/count)*100, 10);
            if ( progress != lastUpdate )
            {
                lastUpdate = progress;
                aOnUpdate( progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        aOnDone("$H$" + s_Itoa64.charAt(countB2) + salt + encode64(hash.words));
    };
    
    window.setTimeout(loopFunc,0);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512s(aPassword, aSalt)
{
    return CryptoJS.SHA512(aSalt + aPassword);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512sb(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var bcrypt = new bCrypt();
    var parts  = aSalt.split(":");
    var config = parts[0];
    var salt   = parts[1];  
    
    var preHash = CryptoJS.SHA512(salt + aPassword).toString();
    
    bcrypt.hashpw( preHash, config, aOnUpdate, function(bfHash) {
        aOnUpdate(100);
        aOnDone(bfHash + ":" + salt);
    });
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512sd(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var parts  = aSalt.split(":");
    var config = parts[0];
    var salt   = parts[1];    
    
    // TODO: Inner workings of PHP crypt with EXT_DES.
    //       Find javascript DES based hasher. CryptoJS.DES.encrypt is slow and
    //       most likely not the correct function.
    
    var preHash = CryptoJS.SHA512(salt + aPassword).toString();
    var hash = CryptoJS.DES.encrypt(preHash, config);
        
    alert("EQDKP password has been hashed using crypt() with CRYPT_EXT_DES."+
          "This is not yet supported due to missing details about the crypt() implementation.\n"+
          "You can set \"USE_CLEARTEXT_PASSWORDS\" to \"true\" in the config to work around this problem.");
    
    aOnUpdate(100);
    aOnDone("_" + key + hash + ":" + salt);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512r(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var parts   = aSalt.split(":");
    var countB2 = parseInt(parts[0], 10);
    var count   = 1 << countB2;
    var salt    = parts[1];
    var salt2   = parts[2];
    
    var preHash = CryptoJS.SHA512(salt + aPassword).toString();
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
            
            var progress = parseInt((round/count)*100, 10);
            if ( progress != lastUpdate )
            {
                lastUpdate = progress;
                aOnUpdate( progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        aOnDone("$S$" + s_Itoa64.charAt(countB2) + salt2 + encode64(hash.words) + ":" + salt);
    };
    
    window.setTimeout(loopFunc,0);
}

// -----------------------------------------------------------------------------

function hash_mybb_md5s(aPassword, aSalt)
{
    return CryptoJS.MD5(CryptoJS.MD5(aSalt) + CryptoJS.MD5(aPassword));
}

// -----------------------------------------------------------------------------

function hash_smf_sha1s(aPassword, aSalt)
{
    return CryptoJS.SHA1(aSalt + aPassword);
}

// -----------------------------------------------------------------------------

function hash_vb3_md5s(aPassword, aSalt)
{
    return CryptoJS.MD5(CryptoJS.MD5(aPassword) + aSalt);
}

// -----------------------------------------------------------------------------

function hash_jml_md5s(aPassword, aSalt)
{
    return CryptoJS.MD5(aPassword + aSalt);
}

// -----------------------------------------------------------------------------

function hash_vanillamd5r(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var parts   = aSalt.split(":");
    var countB2 = parseInt(parts[0], 10);
    var count   = 1 << countB2;
    var salt    = parts[1];
    
    // The original implementation uses md5(..., true) i.e. we're working with the
    // the bytes result and not with the hex representation of it.
    // This requires progressive hashing in CryptoJS.
    
    var hash = CryptoJS.MD5(salt + aPassword);
    var roundHash = CryptoJS.algo.MD5.create();
    var round = 0;
    var lastUpdate = 0;
    
    var loopFunc = function() 
    {
        while (round < count)
        {
            roundHash.update( hash );
            roundHash.update( aPassword );
            hash = roundHash.finalize();
            roundHash.reset();
            ++round;
            
            var progress = parseInt((round/count)*100, 10);
            if ( progress != lastUpdate )
            {
                lastUpdate = progress;
                aOnUpdate( progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        aOnDone("$P$" + s_Itoa64.charAt(countB2) + salt + encode64(hash.words));
    };
    
    window.setTimeout(loopFunc,0);
}