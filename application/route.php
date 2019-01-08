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
    'webList/[:pageNow]/[:pageSize]'    => ['index/index/webList', ['method' => 'get']],
    'getCol'    => ['index/index/getCol', ['method' => 'post']],
    'addWebInfo'    => ['index/index/addWebInfo', ['method' => 'post']],
    'showMSVInfo/[:pageNow]/[:pageSize]'    => ['index/index/showMSVInfo', ['method' => 'get']],
    'showMSInfo/[:pageNow]/[:pageSize]'    => ['index/index/showMSInfo', ['method' => 'get']],
    'showMSVSInfo/[:pageNow]/[:pageSize]'    => ['index/index/showMSVSInfo', ['method' => 'get']],
    'webDel'    => ['index/index/webDel', ['method' => 'post']],
    'update'    => ['index/index/update', ['method' => 'post']],
    'noticeAdd'    => ['index/index/noticeAdd', ['method' => 'post']],
    'noticeList/:domainID/[:pageNow]/[:pageSize]'    => ['index/index/noticeList', ['method' => 'get']],
    'noticeDel'    => ['index/index/noticeDel', ['method' => 'post']],
    'noticeUpdate'    => ['index/index/noticeUpdate', ['method' => 'post']],
    'cntSearch'    => ['index/index/cntSearch', ['method' => 'post']],

    'test/[:name]/[:id]'    => ['index/index/test', ['method' => 'get']],
];
