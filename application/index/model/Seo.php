<?php
namespace app\index\model;


use think\Db;

class Seo extends BaseModel
{
    protected $table = 'web_seo';
    private $pageSize = 30;

    public function getMSInfo($pageNow, $order = [], $pageSize = 0)
    {
        $table = 'main_seo';
        $res = [];
        $column = $this -> getColumn($table);
        $res[0] = $column;
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;
        $seoInfo = $this -> getBaseInfo($pageNow, $order, $pageSize, $table);

//        $numRow = Db::table($table) ->count();
//        $rowData[] = ['numRow' => $numRow];

        return array_merge($res, $seoInfo);
    }

}