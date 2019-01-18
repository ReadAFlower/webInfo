
$(document).ready(function () {

    //初始化
    $.get('http://webinfo.com/webList', function (e) {
        //console.log(e)
        rewrite(e,1);

    });


    //添加input输入框
    $('#ad').click(function () {
        if ($('#do_ad').html()) {
            alert('请输入添加数据');
            var inputNum = $('input').length;
            for (var i = 0; i < inputNum; i++) {
                if ($('input').eq(i).val()){
                    continue;
                }
                $('input').eq(i).focus();
                return false;
            }
            return false;
        }

        var svStatus = $('#server').attr('show');
        var sStatus = $('#seo').attr('show');
        var sv = null;
        var s = null;
       if (svStatus == 'on') {
           sv = 1;
       }
       if (sStatus == 'on') {
           s = 1;
       }

        $.ajax({
            type: 'POST',
            url: 'getCol',
            data: {'sv':sv, 's':s},
            success:function (e) {
                var clo = JSON.parse(e);

                var colLen = $('.tab_tit th').length-1;
                var rowLen = $('.web_list table tr').length;
                var tmpTR = null;
                tmpTR += '<td>'+rowLen+'</td>';
                for (var k in clo){
                    if (k=='domain_id')
                        continue;

                    if (k == 'server_id' || k == 'seo_id')
                        continue;

                    tmpTR += '<td><input type="text" name="'+k+'" value=""></td>';
                }

                tmpTR += '<td><a id="do_ad" href="javascript:;">提交</a> | <a id="cancel" href="javascript:;">取消</a> </td>';
                $('.web_list table').append('<tr>'+tmpTR+'</tr>');
            },
            error:function (e) {
                alert('系统错误');
            }

        })

    });


    //提交添加数据
    $(".web_list table").on('click','#do_ad',function () {

        var svStatus = $('#server').attr('show');
        var sStatus = $('#seo').attr('show');
        var sv = null;
        var s = null;

        if (svStatus == 'on') {
            sv = 1;
        }
        if (sStatus == 'on') {
            s = 1;
        }

        var inputNum = $('#do_ad').parent().siblings().length;

        var adData = {};

        for (var i = 1;i < inputNum;i++) {
            var key = $('#do_ad').parent().siblings().eq(i).children('input').attr('name');
            var val = $('#do_ad').parent().siblings().eq(i).children('input').val();
            adData[key] = val;
        }
        console.log(JSON.stringify(adData));
        console.log(sv);
        console.log(s);
        $.ajax({
            type: 'POST',
            url: 'http://webinfo.com/addWebInfo',
            data: {'webInfo':JSON.stringify(adData), 'sv':sv, 's':s},
            success:function (e) {
                if (e) {

                    var pageNow = 1;
                    $.get('http://webinfo.com/webList/'+pageNow, function (e) {
                        //console.log(e)
                        rewrite(e,pageNow);
                    });
                } else {
                    alert('信息添加失败');
                }
            },
            error:function (e) {
                alert('请求失败');
            }

        })
    });

    //取消添加
    $(".web_list table").on('click', '#cancel', function () {
        //$('#cancel').parent().parent().remove();
        var pageNow = parseInt($('.web_list_pages .page_now').html());
        if (!pageNow) {
            pageNow = 1;
        }
        $.get('http://webinfo.com/webList/'+pageNow, function (e) {

            rewrite(e,pageNow);
        });
    });

    //数据删除
    $('.web_list table').on('click', '.do_del', function () {
        var domainID = $(this).parent().siblings().eq(0).attr('data_id');
        var that = $(this);
        var pageNow = parseInt($('.web_list_pages .page_now').html());
        
        if (confirm('是否确认删除')) {
            $.ajax({
                type: 'POST',
                url: '/webDel',
                data: {'domain_id':domainID},
                success: function (e) {

                    if (e) {
                        if (pageNow < 2 || $('.web_list table tr').length < 3){
                            pageNow = 1;
                        }
                        $.get('http://webinfo.com/webList/'+pageNow, function (e) {
                            //console.log(e)
                            rewrite(e,pageNow);
                        });
                        //alert('删除成功');
                    } else {
                        alert('数据删除失败');
                    }
                },
                error: function (e) {

                    alert('请求失败');
                }
            })
        }

    });


    //web list request page data
    $('.web_list_pages').on('click', 'a', function () {
        var pageNum = parseInt($(this).html());
        var pageNow = null;
        if (pageNum > 0){
            pageNow = pageNum;
        } else {
            pageNum = parseInt($('.web_list_pages .page_now').html());
           if ($(this).attr('class') == 'page_pre') {
               pageNow = pageNum - 1;
           }
            if ($(this).attr('class') == 'page_next') {
                pageNow = pageNum + 1;
            }
        }

        var order = {};

        var dataOrder = $('.web_list .tab_tit th');
        var dataOrderNum = dataOrder.length;
        for (var i = 0; i < dataOrderNum; i++) {
            if (dataOrder.eq(i).attr('isOrder')) {
                order[dataOrder.eq(i).attr('name')] = 'asc';
            }
        }

        requestWebList(pageNow,order);
    });

    //server data add
    $('.web_list').on('click', '#ad_sv', function () {
        var that = $(this);
        var table = 'sv';
        var domainID = $(this).parent().siblings().eq(0).attr('data_id');
        var Tinput = $(this).parent().parent().find('input');
        var inputLen = Tinput.length;
        var svData = {};
        for (var k = 0; k < inputLen; k++) {
            if (!Tinput.eq(k).val()) {
                svData[Tinput.eq(k).attr('name')] = '暂无';
                continue;
            }
            svData[Tinput.eq(k).attr('name')] = Tinput.eq(k).val();
        }
        var pageNow = $('.web_list_pages .page_now').html();

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
                })
                alert('请求失败');

            }
        })
    })

    //server data add  cancel
    $('.web_list').on('click', '#cel_sv', function () {
        var pageNow = $('.web_list_pages .page_now').html();

        if (pageNow < 2){
            pageNow = 1;
        }
        requestWebList(pageNow);
    })

    //order asc
    $('.web_list').on('click', '.tab_tit th',  function() {
        var name = $(this).attr('name');
        var orderArr = ['domain_id', 'domain', 'web_ldomain', 'web_lrat', 'web_srat', 'domain_rat', 'agent_rat', 'ftp_ip', 'mysql_ip'];

        if (orderArr.indexOf(name) >= 0){
            $(this).attr('isOrder','order');
        }

        var thOrder = $('.web_list .tab_tit th');
        var thOrderLen = thOrder.length;
        var order = {};
        order[name] = 'desc';
        for (var i = 0; i < thOrderLen; i++) {
            nowName = thOrder.eq(i).attr('name');
            if (orderArr.indexOf(nowName) >= 0 && nowName != name) {
                if (thOrder.eq(i).attr('isOrder'))  {
                    order[nowName] = 'desc';
                    continue;
                }
            }
            continue;
        }

        var pageNum = parseInt($('.web_list_pages .page_now').html());
        var pageNow = null;
        if (pageNum > 0){
            pageNow = pageNum;
        } else {
            pageNum = parseInt($('.web_list_pages .page_now').html());
            if ($(this).attr('class') == 'page_pre') {
                pageNow = pageNum - 1;
            }
            if ($(this).attr('class') == 'page_next') {
                pageNow = pageNum + 1;
            }
        }

        requestWebList(pageNow,order);
    })
})
