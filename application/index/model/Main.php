<?php
namespace app\index\model;


use think\Db;

class Main extends BaseModel
{
    protected $table = 'web_main';

    public function getList($pageNow = 1, $pageSize = 12)
    {
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : 12;

        $res = [];
        $column = $this->getColumn();
        $res[0] = $column;

        $mainInfo = $this -> getBaseInfo($pageNow, $pageSize);

        $numRow = Db::table($this ->table) ->count();
//        $rowData[] = ['numRow' => $numRow];

        //域名到期
        //即将过期（包括已过期） 2018-2-1  -- 2019-2-1  提醒时间 2019-1-15  now 2019-1-16 2018-2-1
        $domain_up_at = Db::table($this -> table)
            -> where('domain_rat','<', strtotime('-1 year +15 day'))
            -> count();
        //已过期
       $domain_expired = Db::table($this -> table)
            -> where('domain_rat','<', strtotime('-1 year'))
            -> count();
        //即将过期但未过期
        $domain_expire = $domain_up_at - $domain_expired;
//        $rowData[] = ['domain_expire' => $domain_expire];
//        $rowData[] = ['domain_expired' => $domain_expired];

        //主机到期
        //即将过期（包括已过期）
        $host_up_at = Db::table($this -> table)
            -> where('agent_rat','<', strtotime('-1 year +15 day'))
            -> count();
        //已过期
        $host_expired = Db::table($this -> table)
            -> where('agent_rat','<', strtotime('-1 year'))
            -> count();
        //即将过期但未过期
        $host_expire = $host_up_at - $host_expired;
//        $rowData[] = ['host_expire' => $host_expire];
//        $rowData[] = ['host_expired' => $host_expired];

        $rowData[] = [
            'numRow' => $numRow,
            'domain_expire' => $domain_expire,
            'domain_expired' => $domain_expired,
            'host_expire' => $host_expire,
            'host_expired' => $host_expired
        ];

        //对即将过期 时间处理 ，包括已过期
        $nowRow = count($mainInfo);

        for ($i = 0; $i < $nowRow; $i++) {

            if (isset($mainInfo[$i]['domain_rat']) && !empty($mainInfo[$i]['domain_rat'])) {

                $tmpDomainTime = $mainInfo[$i]['domain_rat'];

                if (strtotime($tmpDomainTime) < strtotime('-1 year +15 day')) {
                    $mainInfo[$i]['domain_rat'] = '<span class="red">'.$mainInfo[$i]['domain_rat'].'</span>';
                }
                if (strtotime($tmpDomainTime) < strtotime('-1 year')) {
                    $mainInfo[$i]['domain_rat'] = '<strong>'.$mainInfo[$i]['domain_rat'].'</strong>';
                }
            }
            if (isset($mainInfo[$i]['agent_rat']) && !empty($mainInfo[$i]['agent_rat'])) {
                $tmpHostTime = $mainInfo[$i]['agent_rat'];

                if (strtotime($tmpHostTime) < strtotime('-1 year +15 day')) {
                    $mainInfo[$i]['agent_rat'] = '<span class="red">'.$mainInfo[$i]['agent_rat'].'</span>';
                }
                if (strtotime($tmpHostTime) < strtotime('-1 year')) {
                    $mainInfo[$i]['agent_rat'] = '<strong>'.$mainInfo[$i]['agent_rat'].'</strong>';
                }
            }
        }

        return array_merge($res, $mainInfo, $rowData);
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

    public function getMSVSInfo($pageNow, $pageSize)
    {
        $res = [];
        $table = 'main_server_seo';

        $col = $this -> getColumn($table);
        $res[0] = $col;
        $pageNow = intval($pageNow) > 1 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 1 ? intval($pageSize) : 1;
        $dataList = $this -> getBaseInfo($pageNow, $pageSize, $table);

        $numRow = Db::table($table) ->count();
        $rowData[] = ['numRow' => $numRow];

        return array_merge($res, $dataList, $rowData);

    }
}