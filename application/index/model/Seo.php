<?php
namespace app\index\model;


use think\Db;

class Seo extends BaseModel
{
    protected $table = 'web_seo';

    public function getMSInfo($pageNow, $pageSize)
    {
        $table = 'main_seo';
        $res = [];
        $column = $this -> getColumn($table);
        $res[0] = $column;
        $pageNow = intval($pageNow) > 1 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 1 ? intval($pageSize) : 1;
        $seoInfo = $this -> getBaseInfo($pageNow, $pageSize, $table);

        $numRow = Db::table($table) ->count();
        $rowData[] = ['numRow' => $numRow];

        return array_merge($res, $seoInfo, $rowData);
    }

}