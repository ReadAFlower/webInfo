
    $(document).ready(function () {

        //服务器信息显示和隐藏
        $('#server').click(function () {
            var svStatus = $(this).attr('show');
            var sStatus = $('#seo').attr('show');

            var pageNow = parseInt($('.web_list_pages .page_now').html());
            serverShow(sStatus,svStatus,pageNow);
        });

        //关键词信息显示和隐藏
        $('#seo').click(function () {
            var svStatus = $('#server').attr('show');
            var sStatus = $('#seo').attr('show');
            var pageNow = parseInt($('.web_list_pages .page_now').html());
           seoShow(sStatus,svStatus,pageNow);
        });


        //双击修改数据
        $('.web_list ').on('dblclick', 'td', dbUpdate);

        //search
        $('#do_search').click(function () {
            var sType = $('select[name="search"]').val();
            $('#search_cnt').attr('name',sType);
            var sCnt = $('input[id="search_cnt"]').val();

            $.ajax({
                type: 'POST',
                url: '/cntSearch',
                data: {'search_type' : sType, 'search_cnt' : sCnt},
                success: function (e) {

                    if (e) {
                        $('.main .action .act_btn').css('display','none');
                        var item = JSON.parse(e);
                        var len = item.length-1;

                        var pageSize = item[len]['numRow'];

                        rewrite(e,1, pageSize, true);

                        $('.web_list ').off('dblclick', 'td');
                    } else {
                        alert('关键词查找失败，请重新输入关键词查找');
                    }
                },
                error: function () {
                    alert('服务器请求失败');
                }
            });
        });

        //search cancel
        $('.main .action #cancel_search').click(function () {
            $('.main .action .act_btn').css('display','inline');
            $('select[name="search"]').val('');
            $('input[id="search_cnt"]').val('');

            $.get('http://webinfo.com/webList', function (e) {
                rewrite(e,1);
            });

            $('#seo').attr('show', 'off');
            $('#seo').html('显示SEO信息');
            $('#server').attr('show', 'off');
            $('#server').html('显示服务器信息');

            $('.web_list ').on('dblclick', 'td', dbUpdate);
        });

        //行列冻结
        var tableTop = $('#main_web_list .web_list').offset().top;
        var mainBoxTop = $('#main_web_list').offset().top;

        $('#main_web_list').scroll(function () {

            var top = $('#main_web_list').scrollTop();
            var left = $('#main_web_list').scrollLeft();
            var tr = $('.web_list table tr');
            var trLen = tr.length;
            if (top > tableTop) {
                flgTop = top;
                // console.log('flgtop now');
                $('.web_list table tr th').css({"position":"relative","top":parseInt(top-tableTop)+"px",'border-bottom':'2px solid #cccccc','background':'#CCE8CF'});
                $('.web_list table tr th').eq(0).css({'z-index':'99'});
                $('.web_list table tr th').eq(1).css({'z-index':'99'});
                $('.web_list table tr th').eq(2).css({'z-index':'99'});
            }
            if (top == 0) {
                $('.web_list table tr th').css({"position":"relative","top":"0", 'border-bottom':'0'});
            }
            if (left > 10) {
                tr.eq(0).children().eq(0).css({"position":"relative","left":(left-12)+"px",'border-right':'2px solid #cccccc','z-index':'99','background':'#CCE8CF'});
                tr.eq(0).children().eq(1).css({"position":"relative","left":(left-12)+"px",'border-right':'2px solid #cccccc','z-index':'99','background':'#CCE8CF'});
                tr.eq(0).children().eq(2).css({"position":"relative","left":(left-12)+"px",'border-right':'2px solid #cccccc','z-index':'99','background':'#CCE8CF'});
                for (var i = 1; i < trLen; i++){
                    tr.eq(i).children().eq(0).css({"position":"relative","left":(left-12)+"px",'border-right':'2px solid #cccccc','z-index':'98','background':'#CCE8CF'});
                    tr.eq(i).children().eq(1).css({"position":"relative","left":(left-12)+"px",'border-right':'2px solid #cccccc','z-index':'98','background':'#CCE8CF'});
                    tr.eq(i).children().eq(2).css({"position":"relative","left":(left-12)+"px",'border-right':'2px solid #cccccc','z-index':'98','background':'#CCE8CF'});
                }

                $('#main_web_list .web_list_pages').css({"position":"relative","left":(left-12)+"px"});
                $('#main_web_list .tit').css({"position":"relative","left":(left-12)+"px"});
            }
            if (left < 1){
                tr.eq(0).children().eq(0).css({"position":"relative","left":"0",'border-right':'0','background':'#CCE8CF'});
                tr.eq(0).children().eq(1).css({"position":"relative","left":"0",'border-right':'0','background':'#CCE8CF'});
                tr.eq(0).children().eq(2).css({"position":"relative","left":"0",'border-right':'0','background':'#CCE8CF'});
                for (var i = 1; i < trLen; i++){
                    tr.eq(i).children().eq(0).css({"position":"relative","left":"0",'border-right':'0'});
                    tr.eq(i).children().eq(1).css({"position":"relative","left":"0",'border-right':'0'});
                    tr.eq(i).children().eq(2).css({"position":"relative","left":"0",'border-right':'0'});
                }
                $('#main_web_list .web_list_pages').css({"position":"relative","left":"0"});
                $('#main_web_list .tit').css({"position":"relative","left":"0"});
            }

        });


        //批量导入数据
        $('#insert_webs').click(function () {
            $('#upload_file').css('display','block');
        });

        //取消批量导入数据
        $('#do_cancel').click(function () {
            $('#upload_file').css('display','none');
        });

		//鼠标移入整行高亮问题
		$('.web_list').on('mouseover','table tr',function(){

			$(this).children('td').css('background','#77FD85');
		})
		$('.web_list').on('mouseout','table tr',function(){
			$(this).children('td').css('background','#CCE8CF');
		})

    })



