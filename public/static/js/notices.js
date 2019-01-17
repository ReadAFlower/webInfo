$(document).ready(function () {
    //添加备忘录信息
    $('.web_list table').on('click', '.ad_notice', function () {
        $('.box_notice').css('display', 'block');
        $('#ad_box').css('display', 'block');
        var domainID = $(this).parent().siblings().eq(0).attr('data_id');
        var domain = $(this).parent().siblings().eq(1).html();

        $('#ad_box .icon-memory').html(domain+'：添加备忘录');
        $('#ad_box textarea').val('');
        $('#ad_box .sub a').eq(0).attr('id','ad_notice');
        $('#ad_box .sub a').eq(0).html('添加');
        $('#ad_box input[type="hidden"]').attr('name','domain_id');

        $('input[name="domain_id"]').val(domainID);

    });
    $('#ad_box .icon-close').click(function () {
        $('.box_notice').css('display', 'none');
        $('#ad_box').css('display', 'none');
        $('input[name="domain_id"]').val('');
        $('textarea[name="notice"]').val('');
    });
    $('#rest_notice').click(function () {
        $('textarea[name="notice"]').val('');
    });
    $('#ad_box .sub').on('click', '#ad_notice', function () {
        var domainID = $('input[name="domain_id"]').val();
        var noticeCNT = $('textarea[name="notice"]').val();
        console.log(domainID);
        $.ajax({
            type: 'POST',
            url: '/noticeAdd',
            data: {'notices': JSON.stringify(noticeCNT), 'domainID':domainID},
            success: function (e) {
                if (e) {
                    console.log($('.web_list table td[data_id='+domainID+']').attr('data_id'));
                    var flg = $('.web_list table td[data_id='+domainID+']').parent().children();
                    var flgNum = flg.length-1;

                    $('#ad_box .icon-close').click();
                    //alert('信息添加成功');

                    flg.eq(flgNum).children('.list_notices').click()
                } else {
                    alert('备忘信息添加失败');
                }

            },
            error: function () {
                console.log('请求失败');
            }
        })
    });

    //notice list
    $('.web_list table').on('click', '.list_notices', function () {
        var domain = $(this).parent().parent().children('td[name="domain"]').html();
        var domainID = $(this).parent().parent().children('td[data_id]').attr('data_id');

        $.ajax({
            type: 'GET',
            url: '/noticeList/'+domainID,
            success: function (e) {
                if (e) {
                    console.log(e);

                    $('.box_notice').css('display', 'block');
                    $('#list_box').css('display', 'block');
                    $('#list_box .icon-memory').attr('domain',domain);
                    $('#list_box .icon-memory').html(domain+' 备忘录列表');
                    $('#list_box .icon-memory').attr('domainID',domainID);

                    noticesDataRerite(e,1);

                } else {
                    alert('数据获取失败');
                }
            },
            error:function () {
                alert('服务器请求失败');
            }
        })
    });

    //cancel
    $('#list_box .icon-close').click(function () {
        $('.box_notice').css('display', 'none');
        $('#list_box').css('display', 'none');
        var tmpSTR = $('#list_box table tr:eq(0)').html();
        $('#list_box table').html('');
        $('#list_box table').append('<tr>'+tmpSTR+'</tr>');
        $('#list_box .icon-memory').html('备忘录列表');
    })

    //notice del
    $('#list_box table').on('click', '.notice_del', function () {
        var domain = $('#list_box .icon-memory').attr('domain');
        var domainID = $('#list_box .icon-memory').attr('domainID');
        var noticeID = $(this).parent().parent().attr('noticeID');
        var that = $(this);
        var pageNow = parseInt($('.notice_page .page_now').html());
        console.log(noticeID);
        if (confirm('是否确认删除此信息')) {
            $.ajax({
                type: 'POST',
                url: '/noticeDel',
                data: {'noticeID':noticeID},
                success: function (e) {
                    if (e){
                        if (pageNow < 2 || $('#list_box table tr').length < 3){
                            pageNow = 1;
                        }
                        console.log('pageNow');
                        console.log(pageNow);
                        $.ajax({
                            type: 'GET',
                            url: '/noticeList/'+domainID+'/'+pageNow,
                            success: function (e) {
                                if (e){
                                    noticesDataRerite(e,pageNow);
                                } else {
                                    alert('数据获取失败');
                                }
                            },
                            error: function () {
                                alert('服务器请求失败');
                            }
                        });

                        //alert('删除成功');
                    } else {
                        alert('删除失败');
                    }
                },
                error: function () {
                    alert('请求失败');
                }
            })
        }

    })

    //notice update
    $('#list_box table').on('click', '.notice_update', function () {
        var domainID = $('#list_box .icon-memory').attr('domainID');
        var noticeID = $(this).parent().parent().attr('noticeID');
        var oNoticeCnt = $(this).parent().parent().children('td').eq(1).html();
        var that = $(this);
        console.log(noticeID);
        console.log(domainID);

        $('#list_box .icon-close').click();
        var domain = $('#list_box .icon-memory').attr('domain');
        $('.box_notice').css('display','block');
        $('#ad_box').css('display','block');
        $('#ad_box .icon-memory').html(domain+'：修改备忘信息');
        $('#ad_box textarea').val(oNoticeCnt);
        console.log(oNoticeCnt);
        $('#ad_box .sub a').eq(0).attr('id','notice_update_sub');
        $('#ad_box .sub a').eq(0).html('修 改');
        $('#ad_box form').append('<input type="hidden" name="notice_id" value="'+noticeID+'" >');
        $('#ad_box form input[name="domain_id"]').val(domainID);

    })

    //notice update submit
    $('#ad_box').on('click', '#notice_update_sub', function () {
        var noticeID = $('#ad_box input[name="notice_id"]').val();
        var nNoticeCnt = $('#ad_box textarea[name="notice"]').val();
        var domainID = $('#ad_box input[name="domain_id"]').val();
        $.ajax({
            type: 'POST',
            url: '/noticeUpdate',
            data:{'noticeID': noticeID,'nNoticeCnt':JSON.stringify(nNoticeCnt)},
            success: function (e) {
                console.log(e);
                if (e){
                    $('#ad_box .icon-close').click();
                    var domainTD = $('.web_list td[data_id="'+domainID+'"]').parent().children('td');
                    var domainTDNum = domainTD.length-1;
                    console.log('unpdae success');
                    console.log(domainID);
                    console.log(domainTD.html());
                    domainTD.eq(domainTDNum).children('.list_notices').click();
                    alert('备忘信息修改成功');
                } else {
                    alert('数据修改失败');
                }
            },
            error: function () {
                alert('服务器请求失败');
            }
        })
    });

    //notices list request page data
    $('.notice_page').on('click', 'a', function () {
        var domainID = $('#list_box .icon-memory').attr('domainid')
        var pageNum = parseInt($(this).html());
        var pageNow = null;
        if (pageNum > 0){
            pageNow = pageNum;
        } else {
            pageNum = parseInt($('.notice_page .page_now').html());
            if ($(this).attr('class') == 'page_pre') {
                pageNow = pageNum - 1;
            }
            if ($(this).attr('class') == 'page_next') {
                pageNow = pageNum + 1;
            }
        }

        $.ajax({
            type: 'GET',
            url: '/noticeList/'+domainID+'/'+pageNow,
            success: function (e) {
                if (e){
                    noticesDataRerite(e,pageNow);
                } else {
                    alert('数据获取失败');
                }
            },
            error: function () {
                alert('服务器请求失败');
            }
        });
    });

})