<?php
namespace app\index\model;


use think\Db;

class Main extends BaseModel
{
    protected $table = 'web_main';
    private $pageSize = 30;

    public function getList($pageNow = 1, $order = [], $pageSize = 0)
    {
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;

        $res = [];
        $column = $this->getColumn();
        $res[0] = $column;

        $mainInfo = $this -> getBaseInfo($pageNow, $order, $pageSize);


        return array_merge($res, $mainInfo);
    }

    public function webInsert($data)
    {
        $webInfo = $data;
        $webInfo['web_lrat'] = strtotime($webInfo['web_lrat']);
        $webInfo['web_srat'] = strtotime($webInfo['web_srat']);
        $webInfo['domain_rat'] = strtotime($webInfo['domain_rat']);
        $webInfo['agent_rat'] = strtotime($webInfo['agent_rat']);
        //$res = $this -> db() -> insert($webInfo);

        $res = $this -> db() -> insertGetId($webInfo);
        return $res;
    }

    public function getMSVSInfo($pageNow, $order = [], $pageSize = 0)
    {
        $res = [];
        $table = 'main_server_seo';

        $col = $this -> getColumn($table);
        $res[0] = $col;
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : $this -> pageSize;
        $dataList = $this -> getBaseInfo($pageNow, $order, $pageSize, $table);

//        $numRow = Db::table($table) ->count();
//        $rowData[] = ['numRow' => $numRow];

        return array_merge($res, $dataList);

    }
}