$(document).ready(function () {
    //获取数据选项
    $('#table_list input[type="checkbox"]').click(function () {
        //console.log($(this).prop("checked"));
        var table = '';
        var flgAll = true;
        if ($(this).prop("checked")) {
            if ($(this).val() == 'all') {
                $('#table_list input[name="download_tab"]').prop('checked',true);
                table = 'all';
            }
        } else {
            if ($(this).val() == 'all') {
                $('#table_list input[name="download_tab"]').prop('checked',false);
                table = '';
            }
        }

        if (table != 'all') {
            var tabCheck = $('#table_list input[name="download_tab"]');
            var tabCheckLen = tabCheck.length;
            for (var i = 1; i < tabCheckLen; i++) {
                if (tabCheck.eq(i).prop("checked")) {
                    table += tabCheck.eq(i).val();
                    continue;
                }
                flgAll = false;
            }

            //tabCheck.eq(2).prop('checked',true);
            if (flgAll) {
                table = 'all';
                for (var i = 0; i < tabCheckLen; i++) {
                    tabCheck.eq(i).prop('checked',true);
                }
            } else {
                tabCheck.eq(0).prop('checked',false);

            }
        }

        $.ajax({
            type: 'POST',
            url: '/getImFiled',
            data: {'table':table},
            success: function (e) {

                if (e) {
                    var filedData = JSON.parse(e);
                    var len = filedData.length;

                    $('#filed_datas').html('');
                    $('#filed_datas').append('<span class="download_filed" data="all">全部</span>');

                    for (var i = 0; i < len; i++) {

                        for (var k in filedData[i]) {
                            if (k == 'domain_id' || k == 'server_id' || k == 'seo_id') {
                                continue;
                            } else {
                                $('#filed_datas').append('<span class="download_filed" data="'+k+'">'+filedData[i][k]+'</span>');

                            }
                        }
                    }

                } else {
                    if (table!=''){
                        alert('参数错误，数据请求失败，请重新选择');
                    }
                    $('#filed_datas').html('');
                    $('#filed_datas').append('<span class="download_filed" data="all">全部</span>');
                }
            },
            error: function () {
                alert('服务器请求失败');
            }
        })

    })

    //选择导出数据项
    $('#filed_datas').on('click', '.download_filed', function () {
        if ($(this).attr('data') == 'all') {
            if ($(this).hasClass('checked')) {
                $('#filed_datas span').removeClass('checked');
            }else {
                $('#filed_datas span').addClass('checked');
            }
        } else {
            if ($(this).hasClass('checked')) {
                $(this).removeClass('checked');
            }else {
                $(this).addClass('checked');
            }
            var flgFieldAll = true;
            var field = $('#filed_datas span');
            var fieldLen = field.length;
            for (var i = 1 ; i < fieldLen; i++) {
                if (!field.eq(i).hasClass('checked')) {
                    flgFieldAll = false;
                    break;
                }
            }

            if (flgFieldAll) {
                field.eq(0).addClass('checked');
            } else {
                field.eq(0).removeClass('checked');
            }
        }

    })


    //数据导出选项
    $('#download_data').click(function () {
        $('#download_datas').css('display','block');
    })
    //取消数据导出
    $('#download_cancel').click(function () {
        importClose();
    })
    $('#download_datas .icon-close').click(function () {
        importClose();
    })

    //数据导出
    $('#download_do').click(function () {

        var tabCheck = $('#table_list input[name="download_tab"]');
        var tabCheckLen = tabCheck.length;
        var table = '';
        if (tabCheck.eq(0).prop("checked")){
            table = 'all';

        } else {
            for (var i = 1; i < tabCheckLen; i++) {
                if (tabCheck.eq(i).prop("checked")) {
                    table += tabCheck.eq(i).val();
                    continue;
                }
            }
        }

        if ($('#filed_datas .download_filed').length > 1 && table != '') {

            if ($('#filed_datas .checked').length > 0) {
                var table = '';
                if ($('#table_list input[id="table_all"]').prop('checked')){
                    table = 'all';
                } else {
                    var tabCheck = $('#table_list input[name="download_tab"]');
                    var tabCheckLen = tabCheck.length;
                    for (var i = 1; i < tabCheckLen; i++) {
                        if (tabCheck.eq(i).prop('checked')) {
                            table += tabCheck.eq(i).val();
                        }

                    }
                }

                //获取导出数据项
                var fieldDataArr = {};
                var fieldChecked = $('#filed_datas .checked');
                if (fieldChecked.eq(0).attr('data') != 'all') {
                    var fieldCheckedLen = fieldChecked.length;
                    for (var i = 0 ; i < fieldCheckedLen; i++) {
                        fieldDataArr[i] = fieldChecked.eq(i).attr('data');
                    }
                } else {
                    fieldDataArr[0] = 'all';
                }

                $.ajax({
                    type: 'POST',
                    url: '/importData',
                    data: {'table':table, 'filedArr': JSON.stringify(fieldDataArr)},
                    success: function (e) {

                        if (e) {
                            location.href = e;
                        } else {
                            alert('请求参数错误');
                        }
                    } ,
                    error: function () {
                        alert('服务器请求失败');
                    }
                });

            } else {
                alert('请选择导出数据项');
                return false;
            }
        } else {
            alert('请先选择导出数据表');
            return false;
        }


    })
})