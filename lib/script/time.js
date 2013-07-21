function getDateFromUTC(a_Year, a_Month, a_Day, a_Hour, a_Minute)
{
    var dateTime = new Date();
    dateTime.setUTCFullYear(parseInt(a_Year,10));
    dateTime.setUTCMonth(parseInt(a_Month,10));
    dateTime.setUTCDate(parseInt(a_Day,10));
    dateTime.setUTCHours(parseInt(a_Hour,10));
    dateTime.setUTCMinutes(parseInt(a_Minute,10));

    return dateTime;
}

// -----------------------------------------------------------------------------

function getDateFromUTCString( a_DateString, a_TimeString )
{
    var dateSplit = a_DateString.split("-");
    var timeSplit = a_TimeString.split(":");
    
    var monthSQLToJs = parseInt(dateSplit[1],10)-1;
    return getDateFromUTC( dateSplit[0], monthSQLToJs, dateSplit[2], timeSplit[0], timeSplit[1] );
}

// -----------------------------------------------------------------------------

function leadingZero(a_Number)
{
    return (a_Number < 10) 
        ? "0"+a_Number 
        : a_Number;
}

// -----------------------------------------------------------------------------

function formatTime(a_Year, a_Month, a_Day, a_Hour, a_Minute)
{
    var dateTime = getDateFromUTC(a_Year, a_Month, a_Day, a_Hour, a_Minute);
    var numericHour = dateTime.getHours();
        
    if ( gTimeFormat == 24 )
        return leadingZero(numericHour) + ":" + leadingZero(dateTime.getMinutes());
    
    var postFix = " pm";

    if ( numericHour < 12 )
        postFix = " am";
    else
        numericHour -= 12;

    if ( numericHour === 0 )
        numericHour = 12;

    return leadingZero(numericHour) + ":" + leadingZero(dateTime.getMinutes()) + postFix;
}

// -----------------------------------------------------------------------------

function formatTimeStringUTC( a_DateString, a_TimeString )
{
    var dateSplit = a_DateString.split("-");
    var timeSplit = a_TimeString.split(":");
    
    var monthSQLToJs = parseInt(dateSplit[1],10)-1;
    return formatTime( dateSplit[0], monthSQLToJs, dateSplit[2], timeSplit[0], timeSplit[1] );
}

// -----------------------------------------------------------------------------

function formatDateTime(a_Year, a_Month, a_Day, a_Hour, a_Minute)
{
    var MonthArray  = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));
    var dateTime    = getDateFromUTC(a_Year, a_Month, a_Day, a_Hour, a_Minute);
    var numericHour = dateTime.getHours();
        
    if ( gTimeFormat == 24 )
    {
        return leadingZero(dateTime.getDate()) + ". " + MonthArray[dateTime.getMonth()] + ", " +
            leadingZero(numericHour) + ":" + leadingZero(dateTime.getMinutes());
    }
    
    var timePostFix = " pm";

    if ( numericHour < 12 )
        timePostFix = " am";
    else
        numericHour -= 12;

    if ( numericHour === 0 )
        numericHour = 12;
        
    var dayPostFix = "th";
    
    if ( (dateTime.getDate() < 10) || (dateTime.getDate() > 20) )
    {
        switch (dateTime.getDate() % 10)
        {
        case 1:
            dayPostFix = "st";
            break
        case 2:
            dayPostFix = "nd";
            break;
        case 3:
            dayPostFix = "rd";
            break;
        default:
            dayPostFix = "th";
            break;
        }
    }
    
    return MonthArray[dateTime.getMonth()-1] + " " + leadingZero(dateTime.getDate()) + dayPostFix + ", " +
        leadingZero(numericHour) + ":" + leadingZero(dateTime.getMinutes()) + timePostFix;
}

// -----------------------------------------------------------------------------

function formatDateTimeStringUTC( a_DateString, a_TimeString )
{
    var dateSplit = a_DateString.split("-");
    var timeSplit = a_TimeString.split(":");
    
    var monthSQLToJs = parseInt(dateSplit[1],10)-1;
    return formatDateTime( dateSplit[0], monthSQLToJs, dateSplit[2], timeSplit[0], timeSplit[1] );
}

// -----------------------------------------------------------------------------

function formatDate(a_Year, a_Month, a_Day)
{
    var MonthArray  = Array(L("January"), L("February"), L("March"), L("April"), L("May"), L("June"), L("July"), L("August"), L("September"), L("October"), L("November"), L("December"));
    var dateTime    = getDateFromUTC(a_Year, a_Month, a_Day, 0, 0);
    var numericHour = dateTime.getHours();
        
    if ( gTimeFormat == 24 )
        return leadingZero(dateTime.getDate()) + ". " + MonthArray[dateTime.getMonth()];
    
    var dayPostFix = "th";
    
    if ( (dateTime.getDate() < 10) || (dateTime.getDate() > 20) )
    {
        switch (dateTime.getDate() % 10)
        {
        case 1:
            dayPostFix = "st";
            break
        case 2:
            dayPostFix = "nd";
            break;
        case 3:
            dayPostFix = "rd";
            break;
        default:
            dayPostFix = "th";
            break;
        }
    }
    
    return MonthArray[dateTime.getMonth()-1] + " " + leadingZero(dateTime.getDate()) + dayPostFix;
}

// -----------------------------------------------------------------------------

function formatDateStringUTC( a_DateString )
{
    var dateSplit = a_DateString.split("-");
    
    var monthSQLToJs = parseInt(dateSplit[1],10)-1;
    return formatDate( dateSplit[0], monthSQLToJs, dateSplit[2] );
}

// -----------------------------------------------------------------------------

function formatDateOffsetUTC( a_DateString )
{
    var dateSplit = a_DateString.split("-");    
    var monthSQLToJs = parseInt(dateSplit[1],10)-1;
    
    var dateTime = getDateFromUTC(dateSplit[0], monthSQLToJs, dateSplit[2], 0, 0);
    var gmtString = dateTime.toGMTString().split(" ");
    var offset = gmtString[gmtString.length-1];
    
    return "(" + gmtString[gmtString.length-1] + ")";
}

// -----------------------------------------------------------------------------

function formatHourPrefixed( a_Hour )
{
    var numericHour = parseInt(a_Hour, 10);
        
    if ( gTimeFormat == 24 )
        return leadingZero(numericHour);
        
    var preFix = "pm ";

    if ( numericHour < 12 )
        preFix = "am ";
    else
        numericHour -= 12;

    if ( numericHour === 0 )
        return preFix + "12";

    return preFix + leadingZero(numericHour);
}