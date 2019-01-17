<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'webList/[:pageNow]/[:order]/[:pageSize]'    => ['index/index/webList', ['method' => 'get']],
    'getCol'    => ['index/index/getCol', ['method' => 'post']],
    'addWebInfo'    => ['index/index/addWebInfo', ['method' => 'post']],
    'showMSVInfo/[:pageNow]/[:order]/[:pageSize]'    => ['index/index/showMSVInfo', ['method' => 'get']],
    'showMSInfo/[:pageNow]/[:order]/[:pageSize]'    => ['index/index/showMSInfo', ['method' => 'get']],
    'showMSVSInfo/[:pageNow]/[:order]/[:pageSize]'    => ['index/index/showMSVSInfo', ['method' => 'get']],
    'webDel'    => ['index/index/webDel', ['method' => 'post']],
    'update'    => ['index/index/update', ['method' => 'post']],
    'noticeAdd'    => ['index/index/noticeAdd', ['method' => 'post']],
    'noticeList/:domainID/[:pageNow]/[:pageSize]'    => ['index/index/noticeList', ['method' => 'get']],
    'noticeDel'    => ['index/index/noticeDel', ['method' => 'post']],
    'noticeUpdate'    => ['index/index/noticeUpdate', ['method' => 'post']],
    'cntSearch'    => ['index/index/cntSearch', ['method' => 'post']],
    'formData'    => ['index/index/formData', ['method' => 'post']],
    'getImFiled'    => ['index/index/getImFiled', ['method' => 'post']],
    'importData'    => ['index/index/importData', ['method' => 'post']],
];
