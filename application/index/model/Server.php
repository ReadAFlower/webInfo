<?php
namespace app\index\model;


use think\Db;

class Server extends BaseModel
{
    protected $table = 'web_server';
    private $pageSize = 30;

    public function getMSVInfo($pageNow, $order = [], $pageSize = 0)
    {
        $res = [];
        $table = 'main_server';
        $column = $this->getColumn($table);
        $res[0] = $column;
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;
        $serverInfo = $this -> getBaseInfo($pageNow, $order, $pageSize, $table);

//        $numRow = Db::table($table) ->count();
//        $rowData[] = ['numRow' => $numRow];

        return array_merge($res, $serverInfo);
    }


}