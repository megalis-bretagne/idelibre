var DateUtils = function () {
    var utils = {};




    utils.formatedDate = function (timestamp) {
        var date = new Date(parseInt(timestamp));
        var month = parseInt(date.getMonth()) + 1;
        if (month < 10) {
            month = "0" + month;
        }
        var dayDate = date.getDate();
        if (dayDate < 10) {
            dayDate = "0" + dayDate;
        }
        var fdate = "" + dayDate + "/" + month + "/" + date.getFullYear();
        return fdate;
    }

    utils.formatedDateWithTime = function (timestamp) {

        var fdate = utils.formatedDate(timestamp);
        
        var date = new Date(parseInt(timestamp));
        var hours = date.getHours();
        if (hours < 10) {
            hours = "0" + "" + hours;
        }
        var minutes = date.getMinutes()
        if (minutes < 10) {
            minutes = "0" + minutes;
        }

        var fhour = "" + hours + "h" + minutes;


        return fdate + " " + fhour;

    }

    utils.formattedDateZip = function (timestamp) {
        var date = new Date(parseInt(timestamp));
        var month = parseInt(date.getMonth()) + 1;
        if (month < 10) {
            month = "0" + month;
        }
        var dayDate = date.getDate();
        if (dayDate < 10) {
            dayDate = "0" + dayDate;
        }
        var fdate = "" + dayDate + "_" + month + "_" + date.getFullYear();
        return fdate;
    }


    return utils;
}();
