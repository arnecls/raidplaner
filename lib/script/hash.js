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
        hash_prefixed_md5r(aPassword, '$H$', aSalt, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "wp_md5r":
        hash_prefixed_md5r(aPassword, '$P$', aSalt, aOnUpdate, function(HashedPassword) {
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
        
    case "vanilla_md5r":
        hash_vanilla_md5r(aPassword, aSalt, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "drupal_sha512":
        hash_drupal_sha512(aPassword, aSalt, false, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "drupal_usha512":
        hash_drupal_sha512(aPassword, aSalt, true, aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "drupal_pmd5":
        hash_drupal_md5(aPassword, aSalt, false, "$P$", aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "drupal_upmd5":
        hash_drupal_md5(aPassword, aSalt, true, "U$P$", aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "drupal_hmd5":
        hash_drupal_md5(aPassword, aSalt, false, "$H$", aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    case "drupal_uhmd5":
        hash_drupal_md5(aPassword, aSalt, true, "U$H$", aOnUpdate, function(HashedPassword) {
            aOnDone( finalHash(aKey, HashedPassword) );
        });
        return;
        
    default:
        break;
    }
    
    aOnDone( finalHash(aKey, HashedPassword) );
}

// -----------------------------------------------------------------------------

function getPasswordStrength( aPassword )
{
    var Color   = "#ccc";
    var Quality = 0;
        
    if ( aPassword.length > 0 )
    {
        var CharTypes = new Array( 
            { used : 0, min: 32,  max: 48  },  // special chars 1
            { used : 0, min: 48,  max: 58  },  // number
            { used : 0, min: 58,  max: 65  },  // special chars 2
            { used : 0, min: 65,  max: 91  },  // A-Z
            { used : 0, min: 91,  max: 97  },  // special chars 2
            { used : 0, min: 97,  max: 123 },  // a-z
            { used : 0, min: 123, max: 127 }); // special chars 3
            
        var Colors = new Array(
            { r: 255, g: 0,   b: 0 },
            { r: 255, g: 255, b: 0 },
            { r: 0,   g: 255, b: 0 });
            
        // Analyze charset
    
        for ( var i=0; i < aPassword.length; ++i )
        {
            var CharCode = aPassword.charCodeAt(i);
            for ( var CtIdx=0; CtIdx < CharTypes.length; ++CtIdx )
            {
                if ( (CharCode >= CharTypes[CtIdx].min) && (CharCode < CharTypes[CtIdx].max) )
                {
                    ++CharTypes[CtIdx].used;
                    break;
                }
            }
        }
        
        var VariantBase = 0;
        var AsciiChars  = 0;
        
        for ( var CtIdx=0; CtIdx < CharTypes.length; ++CtIdx )
        {
            if ( CharTypes[CtIdx].used > 0 )
            {
                AsciiChars  += CharTypes[CtIdx].used;
                VariantBase += CharTypes[CtIdx].max - CharTypes[CtIdx].min;
            }
        }
        
        if ( AsciiChars < aPassword.length )
            VariantBase += 32;
        
        // Choose correct Color and progress
        
        Quality = Math.min(1.0, Math.pow(VariantBase, aPassword.length/10.0) / 128.0 );
        
        Color = "#";
        var SegmentSize  = 1.0 / (Colors.length-1);
        var BaseColorIdx = Math.min( parseInt(Quality / SegmentSize, 10), Colors.length-2 );
        var Scale        = (Quality - (SegmentSize * BaseColorIdx)) / SegmentSize;
        
        var MinColor = Colors[BaseColorIdx];
        var MaxColor = Colors[BaseColorIdx+1];
        
        var R = parseInt(MinColor.r * (1-Scale) + MaxColor.r * Scale, 10);
        var G = parseInt(MinColor.g * (1-Scale) + MaxColor.g * Scale, 10);
        var B = parseInt(MinColor.b * (1-Scale) + MaxColor.b * Scale, 10);
        
        Color += ((R<16) ? "0" : "") + R.toString(16);
        Color += ((G<16) ? "0" : "") + G.toString(16);
        Color += ((B<16) ? "0" : "") + B.toString(16);
    }
    
    return {
        color   : Color,
        quality : Quality
    };
}

// -----------------------------------------------------------------------------

function encode64( aWordArray )
{
    // Convert the words of the cryptjs hash to a byte stream in the correct
    // order.
    
    var BytesArray = [];
    for ( var i = 0; i < aWordArray.length; ++i )
    {
        BytesArray.push( (aWordArray[i] >> 24) & 0xFF );
        BytesArray.push( (aWordArray[i] >> 16) & 0xFF );
        BytesArray.push( (aWordArray[i] >> 8) & 0xFF );
        BytesArray.push( aWordArray[i] & 0xFF );
    }
    
    // Process the "string".
    // This code has been ported 1:1 to JS from phpbb3's PHP implementation.
    
    var Encoded = "";
    
    for ( i = 0; i < BytesArray.length; )
    {
       var Value = BytesArray[i++];
       Encoded += s_Itoa64[Value & 0x3f];
    
       if (i < BytesArray.length)
           Value |= BytesArray[i] << 8;
       
       Encoded += s_Itoa64[(Value >> 6) & 0x3f];
       
       if (i++ >= BytesArray.length)
           break;
       
       if (i < BytesArray.length)
           Value |= BytesArray[i] << 16;
       
       Encoded += s_Itoa64[(Value >> 12) & 0x3f];
       
       if (i++ >= BytesArray.length)
           break;
       
       Encoded += s_Itoa64[(Value >>> 18) & 0x3f];
    }
    
    return Encoded;
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

function hash_prefixed_md5r(aPassword, aPrefix, aSalt, aOnUpdate, aOnDone)
{
    var Parts   = aSalt.split(":");
    var CountB2 = parseInt(Parts[0], 10);
    var Count   = 1 << CountB2;
    var Salt    = Parts[1];
    
    // The original implementation uses md5(..., true) i.e. we're working with the
    // the bytes result and not with the hex representation of it.
    // This requires Progressive Hashing in CryptoJS.
    
    var Hash = CryptoJS.MD5(Salt + aPassword);
    var RoundHash = CryptoJS.algo.MD5.create();
    var Round = 0;
    var LastUpdate = 0;
    
    var loopFunc = function() 
    {
        while (Round < Count)
        {
            RoundHash.update( Hash );
            RoundHash.update( aPassword );
            Hash = RoundHash.finalize();
            RoundHash.reset();
            ++Round;
            
            var Progress = parseInt((Round/Count)*100, 10);
            if ( Progress != LastUpdate )
            {
                LastUpdate = Progress;
                aOnUpdate( Progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        aOnDone(aPrefix + s_Itoa64.charAt(CountB2) + Salt + encode64(Hash.words));
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
    var Bcrypt = new bCrypt();
    var Parts  = aSalt.split(":");
    var Config = Parts[0];
    var Salt   = Parts[1];  
    
    var PreHash = CryptoJS.SHA512(Salt + aPassword).toString();
    
    Bcrypt.hashpw( PreHash, Config, aOnUpdate, function(aBfHash) {
        aOnUpdate(100);
        aOnDone(aBfHash + ":" + Salt);
    });
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512sd(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var Parts  = aSalt.split(":");
    var Config = Parts[0];
    var Salt   = Parts[1];    
    
    // TODO: Inner workings of PHP crypt with EXT_DES.
    //       Find javascript DES based hasher. CryptoJS.DES.encrypt is slow and
    //       most likely not the correct function.
    
    var PreHash = CryptoJS.SHA512(Salt + aPassword).toString();
    var Hash = CryptoJS.DES.encrypt(PreHash, Config);
        
    alert("EQDKP password has been hashed using crypt() with CRYPT_EXT_DES."+
          "This is not yet supported due to missing details about the crypt() implementation.\n"+
          "You can set \"USE_CLEARTEXT_PASSWORDS\" to \"true\" in the config to work around this problem.");
    
    aOnUpdate(100);
    aOnDone("_" + Hash + ":" + Salt);
}

// -----------------------------------------------------------------------------

function hash_eqdkp_sha512r(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var Parts   = aSalt.split(":");
    var CountB2 = parseInt(Parts[0], 10);
    var Count   = 1 << CountB2;
    var Salt    = Parts[1];
    var Salt2   = Parts[2];
    
    var PreHash = CryptoJS.SHA512(Salt + aPassword).toString();
    var Hash = CryptoJS.SHA512(Salt2 + PreHash);
    var RoundHash = CryptoJS.algo.SHA512.create();
    var Round = 0;
    var LastUpdate = 0;
    
    var loopFunc = function() 
    {
        while ( Round < Count )
        {
            RoundHash.update( Hash );
            RoundHash.update( PreHash );
            Hash = RoundHash.finalize();
            RoundHash.reset();
            ++Round;
            
            var Progress = parseInt((Round/Count)*100, 10);
            if ( Progress != LastUpdate )
            {
                LastUpdate = Progress;
                aOnUpdate( Progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        aOnDone("$S$" + s_Itoa64.charAt(CountB2) + Salt2 + encode64(Hash.words) + ":" + Salt);
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

function hash_vanilla_md5r(aPassword, aSalt, aOnUpdate, aOnDone)
{
    var Parts   = aSalt.split(":");
    var CountB2 = parseInt(Parts[0], 10);
    var Count   = 1 << CountB2;
    var Salt    = Parts[1];
    
    // The original implementation uses md5(..., true) i.e. we're working with the
    // the bytes result and not with the hex representation of it.
    // This requires Progressive Hashing in CryptoJS.
    
    var Hash = CryptoJS.MD5(Salt + aPassword);
    var RoundHash = CryptoJS.algo.MD5.create();
    var Round = 0;
    var LastUpdate = 0;
    
    var loopFunc = function() 
    {
        while (Round < Count)
        {
            RoundHash.update( Hash );
            RoundHash.update( aPassword );
            Hash = RoundHash.finalize();
            RoundHash.reset();
            ++Round;
            
            var Progress = parseInt((Round/Count)*100, 10);
            if ( Progress != LastUpdate )
            {
                LastUpdate = Progress;
                aOnUpdate( Progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        aOnDone("$P$" + s_Itoa64.charAt(CountB2) + Salt + encode64(Hash.words));
    };
    
    window.setTimeout(loopFunc,0);
}

// -----------------------------------------------------------------------------

function hash_drupal_sha512(aPassword, aSalt, aPreMD5, aOnUpdate, aOnDone)
{
    var Parts   = aSalt.split(":");
    var CountB2 = parseInt(Parts[0], 10);
    var Count   = 1 << CountB2;
    var Salt    = Parts[1];
    
    var Prefix = (aPreMD5) ? "U$S$" : "$S$";
    var Password = (aPreMD5) ? CryptoJS.MD5(aPassword) : aPassword;
    var Hash = CryptoJS.SHA512(Salt + Password);
    var RoundHash = CryptoJS.algo.SHA512.create();
    var Round = 0;
    var LastUpdate = 0;
    
    var loopFunc = function() 
    {
        while ( Round < Count )
        {
            RoundHash.update( Hash );
            RoundHash.update( Password );
            Hash = RoundHash.finalize();
            RoundHash.reset();
            ++Round;
            
            var Progress = parseInt((Round/Count)*100, 10);
            if ( Progress != LastUpdate )
            {
                LastUpdate = Progress;
                aOnUpdate( Progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        
        var CompiledPhrase = Prefix + s_Itoa64.charAt(CountB2) + Salt + encode64(Hash.words);
        var DrupalHashSize = 55; // ...
                
        aOnDone(CompiledPhrase.substring(0, DrupalHashSize));
    };
    
    window.setTimeout(loopFunc,0);
}

// -----------------------------------------------------------------------------

function hash_drupal_md5(aPassword, aSalt, aPreMD5, aPrefix, aOnUpdate, aOnDone)
{
    var Parts   = aSalt.split(":");
    var CountB2 = parseInt(Parts[0], 10);
    var Count   = 1 << CountB2;
    var Salt    = Parts[1];
    
    var Password = (aPreMD5) ? CryptoJS.MD5(aPassword) : aPassword;
    var Hash = CryptoJS.MD5(Salt + aPassword);
    var RoundHash = CryptoJS.algo.MD5.create();
    var Round = 0;
    var LastUpdate = 0;
    
    var loopFunc = function() 
    {
        while (Round < Count)
        {
            RoundHash.update( Hash );
            RoundHash.update( Password );
            Hash = RoundHash.finalize();
            RoundHash.reset();
            ++Round;
            
            var Progress = parseInt((Round/Count)*100, 10);
            if ( Progress != LastUpdate )
            {
                LastUpdate = Progress;
                aOnUpdate( Progress );
                
                window.setTimeout(arguments.callee,0);
                return; // ### return, draw update ###
            }
        }
        
        aOnUpdate(100);
        
        var CompiledPhrase = aPrefix + s_Itoa64.charAt(CountB2) + Salt + encode64(Hash.words);
        var DrupalHashSize = 55; // ...
                
        aOnDone(CompiledPhrase.substring(0, DrupalHashSize));
    };
    
    window.setTimeout(loopFunc,0);
}