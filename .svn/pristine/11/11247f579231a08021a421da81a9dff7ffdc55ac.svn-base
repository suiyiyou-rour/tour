var datePrice = {

    getMonthFirstDay : function(m, y) {
        var d = new Date(y, m, 1);
        return d.getDay();
    },

    getMonthTotalDate : function(m, y) {
        var d = new Date(y, m, 0);
        return d.getDate();
    },

    getFromTodayDays : function(d, m, y) {
        var ret;
        var t = new Date();
        var t1 = new Date();
        t.setFullYear(y);
        t.setMonth(m);
        t.setDate(d);
        var days = Math.floor((t.getTime() - t1.getTime()) / (24 * 60 * 60 * 1000));
        var ret = '今天';
        if (days == 0) {
            ret = '今天';
        }
        else if (days == 1) {
            ret = '明天';
        }
        else if (days == 1) {
            ret = '昨天';
        }
        else if (days > 1) {
            ret = days + '天后';
        }
        else if (days < -1) {
            ret = 0 - days + '天前';
        }
        return ret;
    },

    dataPriceArr : function(m, y) {
        var t = new Date();
        var nSpace = datePrice.getMonthFirstDay(m - 1, y);
        var totalDate = datePrice.getMonthTotalDate(m, y);
        var ret = [];
        var trArray = [];
    
        for (var i = 1; ; i++) {
            if (i <= nSpace) {
                trArray.push('');
            }
            else if (i <= totalDate + nSpace) {
    
                trArray.push({
                    num: i - nSpace,
                    isShowDayInfo: false,
                    isToday: (m == t.getMonth() + 1 && i - nSpace == t.getDate() && y == t.getFullYear()) ? true : false,
                });
            }
            else {
                var l = trArray.length;
                for (var j = 0; j + l < 7; j++) {
                    trArray.push('');
                }
                ret.push(trArray);
                break;
            }
            if (trArray.length == 7) {
                ret.push(trArray);
                trArray = [];
            }
        }
        return ret;
    }
};

if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = datePrice.dataPriceArr;
} else {
    window.datePrice = datePrice.dataPriceArr;
}