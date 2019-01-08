<?php
namespace app\index\model;

use think\Db;

class Notices extends BaseModel
{
    protected $table = 'web_notices';

    public function dataInsert($data)
    {
        if (@!empty($data['notice']) && @!empty($data['domain_id'])){
            $data['notice'] = trim($data['notice']);
            $data['domain_id'] = intval($data['domain_id']);
            $data['notice_at'] = time();
            $res = Notices::create($data);

            return $res;
        }

        return null;
    }

    public function getList($domainID, $page, $pageSize = 7)
    {
        $pageOffset = ((intval($page))-1)*$pageSize;
        $domainID = intval($domainID);
        $resData = Db::name($this->name)
            ->where('domain_id', '=', $domainID)
            ->order('id', 'desc')
            ->limit($pageOffset, $pageSize)
            ->select();
        $numRow = Db::table($this -> table)->where('domain_id', $domainID)->count();
        $rowData[] = ['numRow' => $numRow];

        return array_merge($resData, $rowData);
    }

    public function dataDel($where)
    {
        $noticeID = intval($where);
        $res = Notices::destroy(['id'=>$noticeID]);

        return $res;
    }

    public function updateCnt($data, $id)
    {

        $res = Db::name($this -> name)
            ->where('id',intval($id))
            ->update(['notice' => trim($data)]);

        return $res;

    }
}