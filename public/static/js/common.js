function rewrite(e, pageNow, order = {}, pageSize = 30, search = false) {
    var dataList = JSON.parse(e);
    var dataNum = dataList.length - 1;  //最后一条为分页数据和域名、主机过期信息
    //即将过期信息提示：
    var strTips = '';
    if(dataList[dataNum]['domain_expire']) {
        strTips += dataList[dataNum]['domain_expire'] + '个域名即将过期 &nbsp; ';
    }
    if(dataList[dataNum]['domain_expired']) {
        strTips += dataList[dataNum]['domain_expired'] + '个域名已过期 &nbsp; ';
    }
    if(dataList[dataNum]['host_expire']) {
        strTips += dataList[dataNum]['host_expire'] + '个主机即将到期 &nbsp; ';
    }
    if(dataList[dataNum]['host_expired']) {
        strTips += dataList[dataNum]['host_expired'] + '个主机已到期 &nbsp; ';
    }

    if (strTips) {
        $('.main .tit .tips').remove();
        $('.main .tit').append('<p class="tips">'+strTips+'</p>');
    }
    //console.log(strTips);
    $('.web_list table').html('');
    $('.web_list table').append('<tr class="tab_tit"></tr>');
    $('.web_list_pages').html('');
    $('.web_list_pages').append('<span class="num_row"></span>');
    for (var k in dataList[0]) {

        if (search) {
            if (k == 'domain_id' || k == 'server_id' || k == 'seo_id' || k == 'id') {
                if (order[k]){
                    $('.web_list table .tab_tit').append('<th name="domain_id" isOrder="'+k+'">序号</th>');
                } else {
                    $('.web_list table .tab_tit').append('<th name="domain_id">序号</th>');
                }

                continue;
            }
        }

        if (k=='domain_id') {
            if (order[k]){
                $('.web_list table .tab_tit').append('<th name="domain_id" isOrder="'+k+'">序号</th>');
            } else {
                $('.web_list table .tab_tit').append('<th name="domain_id">序号</th>');
            }
            continue;
        }
        if (k == 'server_id' || k == 'seo_id')
            continue;

        if (order[k]){
            $('.web_list table .tab_tit').append('<th name="'+k+'" isOrder="'+k+'">'+dataList[0][k]+'</th>');
        } else {
            $('.web_list table .tab_tit').append('<th name="'+k+'">'+dataList[0][k]+'</th>');
        }


    }
    // if(!search){
    //     $('.web_list table .tab_tit').append('<th class="do_actions">操作</th>');
    // }
    $('.web_list table .tab_tit').append('<th class="do_actions">操作</th>');


    for (var i = 1; i < dataNum; i++) {
        var n = i;
        var tmp = null;
        var flg = null;
        var nTR = $('<tr></tr>');

        for (var k in dataList[n]) {

            if (search) {
                flg = 'search';
                if (k=='domain_id' || k == 'server_id' || k == 'seo_id' || k == 'id'){

                    nTR.append('<td  data_id = "'+dataList[n][k]+'" table = "'+flg+'">'+i+'</td>');

                    continue;
                }
            }

            if (k=='domain_id'){
                flg = 'm';
                //tmp += '<td data_id = "'+dataList[n][k]+'">'+i+'</td>';
                nTR.append('<td data_id = "'+dataList[n][k]+'" table = "'+flg+'">'+i+'</td>');

                continue;
            }

            if (k == 'server_id') {
                flg = 'sv';
                nTR.attr(k,dataList[n][k]);
                continue;
            }

            if (k == 'seo_id') {
                flg = 's';
                nTR.attr(k,dataList[n][k]);
                continue;
            }

            if (k == 'domain_name') {
                nTR.append('<td class="domain_name" name="'+k+'" table = "'+flg+'">'+dataList[n][k]+'</td>');
                continue;
            }
            if (k == 'agent') {
                nTR.append('<td class="agent" name="'+k+'" table = "'+flg+'">'+dataList[n][k]+'</td>');
                continue;
            }
            //tmp += '<td name="'+k+'">'+dataList[n][k]+'</td>';
            nTR.append('<td name="'+k+'" table = "'+flg+'">'+dataList[n][k]+'</td>');
        }
        // if (!search) {
        //     nTR.append('<td class="do_actions"><a class="ad_notice" href="javascript:;">添加备忘录</a> | <a class="list_notices" href="javascript:;">查看备忘录信息</a> | <a class="do_del" href="javascript:;">删除</a></td>');
        // }
        nTR.append('<td class="do_actions"><a class="ad_notice" href="javascript:;">添加备忘录</a> | <a class="list_notices" href="javascript:;">查看备忘录信息</a> | <a class="do_del" href="javascript:;">删除</a></td>');
       // $('table').append('<tr>'+tmp+'</tr>');
        $('.web_list table').append(nTR);
    }

    //分页导航
    var numRow = dataList[dataNum]['numRow'];

    pageNav('.web_list_pages', numRow, pageNow, pageSize);
}

function pageNav(pageOBJ, numRow, pageNow, pageSize) {
    var pageSize = pageSize;
    var pageNow = pageNow;
    var numRow = numRow;
    var maxSize = null;
    $(pageOBJ+' .num_row').html('总共'+numRow+'条数据');
    var maxSize = Math.ceil(numRow/pageSize);

    if (maxSize < 2)
        return null;

    if (pageNow < 5) {

        if (pageNow > 1) {
            $(pageOBJ).append('<a class="page_pre" href="javascript:;">上一页</a>');
        }
        if(maxSize > 10){
            maxSize = 10;
        }
        for (var u = 0 ; u < maxSize; u++) {
            var n = u+1;
            if (n == pageNow){
                $(pageOBJ).append('<span class="page_now">'+n+'</span>');
                continue;
            }
            $(pageOBJ).append('<a href="javascript:;">'+n+'</a>');
        }

        if (pageNow < maxSize){
            $(pageOBJ).append('<a class="page_next" href="javascript:;">下一页</a>');
        }

    } else {
        if (maxSize < pageNow+5){
            var tmpMaxSize = maxSize;
        } else {
            tmpMaxSize = pageNow+5;
        }

        var tmpMaxSize = pageNow + 5;

        $(pageOBJ).append('<a class="page_pre" href="javascript:;">上一页</a>');
        for (var u = pageNow - 5; u < tmpMaxSize; u++) {
            var n = u+1;

            if (n > maxSize)
                break;

            if (n == pageNow){
                $(pageOBJ).append('<span class="page_now">'+n+'</span>');
                continue;
            }
            $(pageOBJ).append('<a href="javascript:;">'+n+'</a>');
        }

        if (pageNow < maxSize){
            $(pageOBJ).append('<a class="page_next" href="javascript:;">下一页</a>');
        }

    }

    $('.web_list tr').each(function () {
        var n = $(this).index();
        if (n){
            if ($(this).children('td').eq(3).html() == '关闭'){
                $(this).children('td').css('background','rgb(255,255,0)');
            }
        }

    })
}


function noticesDataRerite(e,pageNow) {

    $('#list_box table').html('');
    $('#list_box table').append('<tr><th class="list_no">序号</th><th class="notice_cnt">备忘内容</th><th class="at_time">添加时间</th><th class="notice_do">操作</th></tr>');
    $('#list_box .notice_page').html('');
    $('#list_box .notice_page').append('<span class="num_row"></span>');

    var noticeList = JSON.parse(e);
    var listNum = noticeList.length-1;
    //console.log(listNum);
    for (var i = 0; i < listNum; i++) {
        var n = i+1;
        $('#list_box table').append('<tr noticeID="'+noticeList[i]['id']+'"><td>'+n+'</td><td>'+noticeList[i]['notice']+'</td><td>'+noticeList[i]['notice_at']+'</td><td><a class="notice_update" href="javascript:;">修 改</a> | <a class="notice_del" href="javascript:;">删 除</a> </td></tr>');
    }

    //分页导航
    var numRow = noticeList[listNum]['numRow'];
    var pageNow = pageNow;
    var pageSize = 7;
    pageNav('.notice_page', numRow, pageNow, pageSize);
}

function dbUpdate() {
    if ($('.web_list table input').length>0 || $('.web_list table select').length>0){
        return false;
    }
    var domainID = $(this).siblings().eq(0).attr('data_id');

    var thNum = $('.web_list table th').length;
    var tdRow = $('.web_list table tr').length-1;
    var tdNum = $('.web_list table tr td').length;

    for (var i=0 ; i<tdNum; i++) {
        for (var j=0; j<tdRow; j++) {
            var n1 = j*thNum;
            var n2 = (j+1)*thNum-1;

            if ($(this).index() == n1 || $(this).index() == n2) {
                return false;
            }
        }
    }
    var tmpVal = '';
    var table = $(this).attr('table');
    if ($(this).children('.red').length > 0) {
        var spanFlg = true;
        tmpVal = $(this).children('.red').html();
    } else if ($(this).children('strong').length > 0) {
        var strongFlg = true;
        tmpVal = $(this).children('strong').children('.red').html();
    }else{
        tmpVal = $(this).html();
    }
    $(this).html('');
    var tmpName = $(this).attr('name');
    if (tmpName == 'web_status') {
        if (tmpVal == '监控'){
            tmpNum=1;
        } else {
            tmpNum=2;
        }
        $(this).append('<select name="'+tmpName+'"><option value="'+tmpNum+'" selected>'+tmpVal+'</option><option value="1">监控</option><option value="2">关闭</option></select>');
    }else if (tmpName=='web_type'){
        if (tmpVal == 'PC'){
            tmpNum=1;
        } else if (tmpVal == '移动站') {
            tmpNum=2;
        }else {
            tmpNum=2;
        }
        $(this).append('<select name="'+tmpName+'"><option value="'+tmpNum+'" selected>'+tmpVal+'</option><option value="1">PC</option><option value="2">移动站</option><option value="3">自适应</option></select>');
    }
    else if (tmpName=='is_mobile'){
        if (tmpVal == '无'){
            tmpNum=1;
        } else{
            tmpNum=2;
        }
        $(this).append('<select name="'+tmpName+'"><option value="'+tmpNum+'" selected>'+tmpVal+'</option><option value="1">无</option><option value="2">有</option></select>');
    }else{
        $(this).append('<input type="text" name="'+tmpName+'" value="'+tmpVal+'" table = "'+table+'">');
        $(this).children('input').focus();
    }


    if (table == 'sv' && $('#server').attr('show') == 'on' && !$(this).parent().attr('server_id')){
        console.log(table);
        $(this).html('');
        $(this).append('<input type="text" name="'+tmpName+'" value="" table = "'+table+'">');
        var sibLen = $(this).siblings().length;
        for (var i = 0; i < sibLen; i++) {

            if ($(this).siblings().eq(i).attr('table')=='sv'){

                //server数据为空时所有server项数据填写完成再提交
                if ($(this).siblings().eq(i).find('input').val()){
                    continue;
                }

                $(this).siblings().eq(i).html('');
                var Tname = $(this).siblings().eq(i).attr('name');
                $(this).siblings().eq(i).append('<input type="text" name="'+Tname+'" value="" table = "'+table+'">');
            }
        }

        $('#ad_sv').remove();
        $('#cel_sv').remove();
        $(this).siblings().eq(sibLen-1).append(' <a id="ad_sv" href="javascript:;">添加服务器信息</a>  <a id="cel_sv" href="javascript:;">取消</a>');

        var that = $(this);
        $(this).parent().on('blur', 'input', function () {
            var pageNow = $('.web_list_pages .page_now').html();
            var Tinput = that.parent().find('input');
            var inputLen = Tinput.length;
            var numFlg = -1;
            var svData = {};
            for (var k = 0; k < inputLen; k++) {
                if (!Tinput.eq(k).val()) {
                    numFlg = k;
                    break;
                }
                svData[Tinput.eq(k).attr('name')] = Tinput.eq(k).val();

            }

            if (numFlg<0){

                if (!svData['ftp_ip'] || !svData['mysql_ip']) {
                    return false;
                }

                $.ajax({
                    type: 'POST',
                    url: '/update',
                    data: {'field':JSON.stringify(svData), 'domainID':domainID, 'table':table},
                    success: function (e) {

                        if (e) {

                            if (pageNow < 2){
                                pageNow = 1;
                            }
                            requestWebList(pageNow);
                        } else {
                            alert('信息插入失败');
                        }

                    },
                    error: function () {

                        Tinput.each(function () {
                            var Nname = $(this).attr('name');
                            $(this).parent().attr('name',Nname);
                            $(this).parent().html('null');
                        });
                        alert('请求失败');

                    }
                });

            }

        });

        return false;
    }

    //input绑定失去焦点事件
    $(this).on('blur', 'input', function () {

        var that = $(this);
        var val = $(this).val();
        var name = $(this).attr('name');
        var filedData = {};
        filedData[name] = val;
        var pageNow = $('.web_list_pages .page_now').html();

        if (val!=tmpVal) {
            $.ajax({
                type: 'POST',
                url: '/update',
                data: {'field':JSON.stringify(filedData), 'domainID':domainID, 'table':table},
                success: function (e) {
                    if (e) {

                        if (pageNow < 2){
                            pageNow = 1;
                        }

                        requestWebList(pageNow);
                    } else {
                        $.get('http://webinfo.com/webList/'+pageNow, function (e) {
                            //console.log(e)
                            rewrite(e,pageNow);
                        });
                        alert('数据修改失败');
                    }
                },
                error: function () {
                    alert('数据修改失败');
                    that.parent().html(tmpVal);
                }
            });
        } else {
            $(this).parent().html(tmpVal);

        }
    });
    
    //select onchange
    $(this).on('change', 'select', function () {
        console.log($(this).val());
        console.log($(this).attr('name'));
        var that = $(this);
        var val = $(this).val();
        var name = $(this).attr('name');
        var filedData = {};
        filedData[name] = val;
        var pageNow = $('.web_list_pages .page_now').html();

        if (val!=tmpVal) {
            $.ajax({
                type: 'POST',
                url: '/update',
                data: {'field':JSON.stringify(filedData), 'domainID':domainID, 'table':'m'},
                success: function (e) {
                    if (e) {

                        if (pageNow < 2){
                            pageNow = 1;
                        }

                        requestWebList(pageNow);
                    } else {
                        $.get('http://webinfo.com/webList/'+pageNow, function (e) {
                            //console.log(e)
                            rewrite(e,pageNow);
                        });
                        alert('数据修改失败');
                    }
                },
                error: function () {
                    alert('数据修改失败');
                    that.parent().html(tmpVal);
                }
            });
        } else {
            $(this).parent().html(tmpVal);

        }
    })

}


//server信息的显示和隐藏
function serverShow(sStatus,svStatus,pageNow) {

    if (sStatus == 'off'){
        if (svStatus =='off'){
            $.get('/showMSVInfo/'+pageNow,function (e) {

                rewrite(e, pageNow);

                $('#server').attr('show', 'on');
                $('#server').html('隐藏服务器信息');
            });
        } else {
            $.get('/webList/'+pageNow,function (e) {

                rewrite(e, pageNow);

                $('#server').attr('show', 'off');
                $('#server').html('显示服务器信息');
            });
        }
    }else{
        if (svStatus == 'off') {
            $.get('/showMSVSInfo/'+pageNow,function (e) {

                rewrite(e, pageNow);

                $('#server').attr('show', 'on');
                $('#server').html('隐藏服务器信息');
            });
        } else {
            $.get('/showMSInfo/'+pageNow,function (e) {

                rewrite(e, pageNow);

                $('#server').attr('show', 'off');
                $('#server').html('显示服务器信息');
            });
        }
    }
}

//seo 信息的显示和隐藏
function seoShow(sStatus,svStatus,pageNow) {

    if (svStatus == 'off'){
        if (sStatus =='off'){
            $.get('/showMSInfo/'+pageNow,function (e) {

                rewrite(e, pageNow);

                $('#seo').attr('show', 'on');
                $('#seo').html('隐藏SEO信息');
            });
        } else {
            $.get('/webList/'+pageNow,function (e) {

                rewrite(e, pageNow);

                $('#seo').attr('show', 'off');
                $('#seo').html('显示SEO信息');
            });
        }
    }else{
        if (sStatus == 'off') {
            $.get('/showMSVSInfo/'+pageNow,function (e) {

                rewrite(e,pageNow);

                $('#seo').attr('show', 'on');
                $('#seo').html('隐藏SEO信息');
            });
        } else {
            $.get('/showMSVInfo/'+pageNow,function (e) {

                rewrite(e, pageNow);

                $('#seo').attr('show', 'off');
                $('#seo').html('显示SEO信息');
            });
        }
    }
}


//weblist request update or pagelist
function requestWebList(pageNow,order) {
    var tmpURL = '/showM';
    var svStatus = $('#server').attr('show');
    var sStatus = $('#seo').attr('show');
    if (svStatus == 'on'){
        tmpURL += 'SV';
    }
    if (sStatus == 'on'){
        tmpURL += 'S';
    }
    if (tmpURL.length > 6){
        tmpURL += 'Info/';
    } else {
        tmpURL = '/webList/';
    }

    $.ajax({
        type: 'GET',
        url: tmpURL+pageNow+'/'+JSON.stringify(order),
        success: function (e) {
            if (e) {
                rewrite(e,pageNow, order);
            } else {
                alert('数据获取失败');
            }
        },
        error:function () {
            alert('服务器请求失败');
        }
    })
}

//取消数据导出
function importClose() {
    $('#filed_datas').html('');
    $('#filed_datas').append('<span class="download_filed" data="all">全部</span>');
    var tabCheck = $('#table_list input[name="download_tab"]');
    var tabCheckLen = tabCheck.length;
    for (var i = 0; i < tabCheckLen; i++) {
        tabCheck.eq(i).prop('checked',false);
    }
    $('#download_datas').css('display','none');
}