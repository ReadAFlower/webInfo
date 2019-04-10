<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"D:\phpstudy\WWW\webinfo.com\public/../application/index\view\index\index.html";i:1551403753;}*/ ?>
<html lang="zh">
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title>网站信息记录</title>
    <script type="text/javascript" src="/static/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="/static/js/common.js"></script>
    <script type="text/javascript" src="/static/js/main.js"></script>
    <script type="text/javascript" src="/static/js/other.js"></script>
    <script type="text/javascript" src="/static/js/dataImport.js"></script>
    <script type="text/javascript" src="/static/js/notices.js"></script>
	<link rel="stylesheet" href="/static/css/iconfont.css">
    <link rel="stylesheet" href="/static/css/main.css">
</head>
<body>
    <div class="main" id="main_web_list">
        <div class="tit">
            <h1>网站列表</h1>
            <p class="action" style="margin-left: 20px;height: 28px;line-height: 28px;font-size: 18px;">
                <span class="act_btn">
                    <a id="ad" href="javascript:;">添加</a> |
                    <a id="server" show="off" href="javascript:;">显示服务器信息</a> |
                    <a id="seo" show="off" href="javascript:;">显示SEO信息</a> |
                    <a id="insert_webs" href="javascript:;">批量导入</a> |
                    <a id="download_data" href="javascript:;">数据导出</a>
                </span>
                <span class="search_box"><strong>快速查找：</strong>
                    类别：<select name="search" style="width: 100px;">
                        <option value=""></option>
                        <option value="main_d">域名</option>
                        <option value="main_n">网站名</option>
                        <option value="notice">备忘录信息</option>
                        <option value="seo">SEO关键词</option>
                        <option value="server">服务器信息</option>
                    </select>&nbsp;
                    关键词：<input type="text" id="search_cnt" value="" placeholder="请输入查找关键信息" style="width: 120px;">
                    <a id="do_search" href="javascript:;">查 找</a>
                    <a id="cancel_search" href="javascript:;">返回网站列表</a>
                </span>
            </p>
        </div>
        <div class="web_list">
            <table border="1" cellpadding="0" cellspacing="0">
            </table>
        </div>
        <div class="web_list_pages">
            <span class="num_row"></span>

        </div>
    </div>

    <div class="box_notice">
        <div id="ad_box">
			<div class="box_head"><i class="icon iconfont icon-memory"></i><i class="icon iconfont icon-close"></i></div>
			<form>
				<input type="hidden" name="domain_id" value="">
				<textarea name="notice" rows="5" placeholder="请输入备忘信息"></textarea>
				<div class="sub">
					<a href="javascript:;"></a>
					<a id="rest_notice" href="javascript:;">重 置</a>
				</div>
			</form>
        </div>

        <div id="list_box">
            <div class="box_head"><i class="icon iconfont icon-memory">备忘录列表</i><i class="icon iconfont icon-close"></i></div>
            <div style="overflow: hidden;overflow-y:scroll;height: 450px;width: 100%;">
                <table border="1" cellpadding="0" cellspacing="0">
                    <tr><th class="list_no">序号</th><th class="notice_cnt">备忘内容</th><th class="at_time">添加时间</th><th class="notice_do">操作</th></tr>
                </table>
            </div>
            <div class="notice_page">
                <span class="num_row"></span>
            </div>
        </div>
    </div>

    <div id="upload_file">
        <form action="" method="post" enctype="multipart/form-data" id="file_form">
            <input type="hidden" name="file_data_import" value="1">
            <span><i class="icon iconfont icon-ziyuanbaosongshujudaoru" style="display:inline;padding-right:5px;"></i>上传数据文件：</span><input type="file" name="file_datas" id="file_datas" accept="application/vnd.ms-excel,text/plain">
            <input type="submit" id="do_insert" value="提交"><a id="do_cancel" href="javascript:;">取消</a>
        </form>
    </div>

    <div id="download_datas">
        <div class="download_box" style="width: 35%;background: #fff;margin:10% auto;height: 50%;">
            <span style="display: block;width: 100%;line-height: 18px;background: #ccc;"><i class="icon iconfont icon-ziyuanbaosongshujudaochu" style="display:inline;padding-right:5px;">数据导出</i><i class="icon iconfont icon-close"></i></span>
            <div>
                <span id="table_list" style="display: block;line-height: 24px;margin: 15px auto;width: 90%;">
                    <strong>请选择导出数据表：</strong><br>
                    <input type="checkbox" name="download_tab" id="table_all" value="all">全部导出
                    <input type="checkbox" name="download_tab" value="m">网站主信息
                    <input type="checkbox" name="download_tab" value="sv">服务器信息
                    <input type="checkbox" name="download_tab" value="s">关键词信息
                </span>
                <span style="display: block;width: 90%;line-height: 21px;height: 50%;margin: 5px auto;">
                    <strong>请选择导出数据项：</strong><br>
                    <span id="filed_datas" style="display:block;overflow-y: scroll;width: 100%;height: 90%;border:1px double #ccc; ">
                        <span class="download_filed" data="all">全部</span>
                    </span>
                </span>
                <span id="download_btn"><a id="download_do" href="javascript:;">导出</a><a id="download_cancel" href="javascript:;">取消</a></span>
            </div>
        </div>
    </div>
</body>
</html>
