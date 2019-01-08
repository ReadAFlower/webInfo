<?php
namespace app\index\model;


use think\Db;

class Server extends BaseModel
{
    protected $table = 'web_server';

    public function getMSVInfo($pageNow, $pageSize)
    {
        $res = [];
        $table = 'main_server';
        $column = $this->getColumn($table);
        $res[0] = $column;
        $pageNow = intval($pageNow) > 1 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 1 ? intval($pageSize) : 1;
        $serverInfo = $this -> getBaseInfo($pageNow, $pageSize, $table);

        $numRow = Db::table($table) ->count();
        $rowData[] = ['numRow' => $numRow];

        return array_merge($res, $serverInfo, $rowData);
    }


}