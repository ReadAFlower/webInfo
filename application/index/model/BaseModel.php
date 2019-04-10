<?php
namespace app\index\model;

use think\Db;
use think\Exception;
use think\Model;
use think\Url;

class BaseModel extends Model
{

    public function getColumn($table= '')
    {
        $table = !empty($table) ? htmlspecialchars($table) : $this -> table;

        $res = Db::query('SHOW FULL COLUMNS FROM '.$table);
        $len = count($res);
        $col = [];
        for ($i=0; $i < $len; $i++) {
            $col[$res[$i]['Field']] = $res[$i]['Comment'];
        }
        return $col;
    }

    public function getBaseInfo($pageNow = 1, $order = [], $pageSize = 30, $table= '')
    {
        if (empty($table)){
            $table = $this -> table;
        }
        $table = !empty($table) ? htmlspecialchars($table) : $this -> table;
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : 30;

        if (count($order) < 1) {
            $order = ['domain_id'=>'desc'];
        }
        $order = array_merge(['web_status'=>'desc'], $order);
        //$Info = Db::query('SELECT * FROM '.$table);
        $offset = ($pageNow-1) * $pageSize;

//        //不分级数据查询
//        $Info = Db::table($table) -> order($order) -> limit($offset,$pageSize) -> select();


        //分级查询数据
        //已关闭监控数据
        $InfoOff = Db::table($table)
            -> where('web_status', '=', '关闭')
            -> order($order)
            -> limit($offset,$pageSize)
            -> select();

        $InfoOffCount = count($InfoOff);

        if ($InfoOffCount < $pageSize){

            //主机到期数据（即将到期+已到期）
            $InfoSOff = Db::table($table)
                -> where('web_status', '=', '监控')
                -> where('agent_rat', '<', strtotime('-1 year +15 day'))
                -> order($order)
                -> limit($offset,$pageSize-$InfoOffCount)
                -> select();

            $InfoSOffCount = count($InfoSOff);

            if ($InfoSOffCount+$InfoOffCount < $pageSize){

                //域名到期数据（即将到期+已到期）
                $InfoDOff = Db::table($table)
                    -> where('web_status', '=', '监控')
                    -> where('agent_rat', '>', strtotime('-1 year +15 day'))
                    -> where('domain_rat', '<', strtotime('-1 year +15 day'))
                    -> order($order)
                    -> limit($offset,$pageSize-$InfoOffCount-$InfoSOffCount)
                    -> select();

                $InfoDOffCount = count($InfoDOff);

                if ($InfoSOffCount+$InfoOffCount+$InfoDOffCount < $pageSize){

                    $InfoOffCountAll = Db:: table($table)
                        -> where('web_status', '=', '关闭')
                        ->count();
                    $InfoSOffCountAll = Db:: table($table)
                        -> where('web_status', '=', '监控')
                        -> where('agent_rat', '<', strtotime('-1 year +15 day'))
                        ->count();
                    $InfoDOffCountAll = Db:: table($table)
                        -> where('web_status', '=', '监控')
                        -> where('agent_rat', '>', strtotime('-1 year +15 day'))
                        -> where('domain_rat', '<', strtotime('-1 year +15 day'))
                        ->count();

                    if ($InfoOffCountAll+$InfoSOffCountAll+$InfoDOffCountAll<($pageNow-1)*$pageSize){
                        $offset = $offset - ($InfoOffCountAll+$InfoSOffCountAll+$InfoDOffCountAll)%$pageSize;
                    }
                    $InfoOn = Db::table($table)
                        -> where('web_status', '=', '监控')
                        -> where('agent_rat', '>', strtotime('-1 year +15 day'))
                        -> where('domain_rat', '>', strtotime('-1 year +15 day'))
                        -> order($order)
                        -> limit($offset,$pageSize-$InfoOffCount-$InfoSOffCount-$InfoDOffCount)
                        -> select();


                    $Info = array_merge($InfoOff, $InfoSOff, $InfoDOff, $InfoOn);
                }else{
                    $Info = array_merge($InfoOff, $InfoSOff, $InfoDOff);
                }

            }else{

                $Info = array_merge($InfoOff, $InfoSOff);

            }

        }else{
            $Info = $InfoOff;
        }

        $InfoRow = count($Info);

        for ($i = 0; $i < $InfoRow; $i++){
            if (isset($Info[$i]['web_lrat'])) {
                $Info[$i]['web_lrat'] = date('Y-m-d', $Info[$i]['web_lrat']);
            }
            if (isset($Info[$i]['web_srat'])) {
                $Info[$i]['web_srat'] = date('Y-m-d', $Info[$i]['web_srat']);
            }
            if (isset($Info[$i]['domain_rat'])) {
                $Info[$i]['domain_rat'] = date('Y-m-d', $Info[$i]['domain_rat']);
            }
            if (isset($Info[$i]['agent_rat'])) {
                $Info[$i]['agent_rat'] = date('Y-m-d', $Info[$i]['agent_rat']);
            }
            foreach ($Info[$i] as $key => $val) {
                if (empty($Info[$i][$key])) {
                    if ($key == 'server_id' || $key == 'seo_id'){
                        continue;
                    }
                    $Info[$i][$key] = '暂无';
                }
            }
        }

        $numRow = Db::table($table) ->count();


        //域名到期
        //即将过期（包括已过期） 2018-2-1  -- 2019-2-1  提醒时间 2019-1-15  now 2019-1-16 2018-2-1
        $domain_up_at = Db::table($table)
            -> where('domain_rat','<', strtotime('-1 year +15 day'))
            -> where('web_status', '=', '监控')
            -> count();

        //已过期
        $domain_expired = Db::table($table)
            -> where('web_status', '=', '监控')
            -> where('domain_rat','<', strtotime('-1 year'))
            -> count();
        //即将过期但未过期
        $domain_expire = $domain_up_at - $domain_expired;

        //主机到期
        //即将过期（包括已过期）
        $host_up_at = Db::table($table)
            -> where('web_status', '=', '监控')
            -> where('agent_rat','<', strtotime('-1 year +15 day'))
            -> count();
        //已过期
        $host_expired = Db::table($table)
            -> where('web_status', '=', '监控')
            -> where('agent_rat','<', strtotime('-1 year'))
            -> count();

        //即将过期但未过期
        $host_expire = $host_up_at - $host_expired;

        $rowData[] = [
            'numRow' => $numRow,
            'domain_expire' => $domain_expire,
            'domain_expired' => $domain_expired,
            'host_expire' => $host_expire,
            'host_expired' => $host_expired
        ];

        $mainInfo = $Info;
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

        return array_merge($mainInfo, $rowData);
    }

    public function del($domainID, $field)
    {
        $domainID = intval($domainID);
//        $checkID = self::where('domain_id',$domainID)->value($field);
        $res = self::destroy(['domain_id' => $domainID]);

        return $res;
    }

    public static function datChange($data, $domainID, $table = 'main_server_seo')
    {
        $domainID = intval($domainID);
        $table = htmlspecialchars($table);
        $check = Db::table($table) -> where('domain_id',$domainID) -> find();

        if (!$check) {
            $data['domain_id'] = $domainID;

            $resID = Db::table($table) -> insertGetId($data);

            return $resID;
        } else {
            $res = Db::table($table)->where('domain_id',$domainID)->update($data);
            return $res;
        }

    }

    //快速查找
    public static function search($type, $cnt)
    {
        $cnt = htmlspecialchars($cnt);
        switch ($type) {
            case 'main_d':
                $table = 'web_main';
                $field = 'domain';
                $col[0] = (new self()) -> getColumn($table);
                $res = Db::table($table)
                    -> where($field, 'like', '%'.trim($cnt).'%')
                    -> select();
                break;
            case 'main_n':
                $table = 'web_main';
                $field = 'domain_name';
                $col[0] = (new self()) -> getColumn($table);
                $res = Db::table($table)
                    -> where($field, 'like', '%'.trim($cnt).'%')
                    -> select();
                break;
            case 'server':
                $table = 'web_server';
                //$field = 'ftp_ip';
                $field = 'domain';
                $col[0] = [
                    'server_id' => '服务器信息ID',
                    'domain' => '网站域名',
                    'ftp_ip' => 'FTP地址',
                    'ftp_user' => 'FTP登录名',
                    'ftp_pwd' => 'FTP密码',
                    'mysql_ip' => '数据库地址',
                    'mysql_user' => '数据库登录名',
                    'mysql_pwd' => '数据库密码',
                    'console_domain' => '控制台地址',
                    'console_user' => '控制台登录名',
                    'console_pwd' => '控制台密码',
                ];
                $res = Db::table($table)
                    -> alias('s')
                    -> join('web_main m','s.domain_id = m.domain_id')
                    -> field('s.server_id, m.domain, s.ftp_ip, s.ftp_user, s.ftp_pwd, s.mysql_ip, s.mysql_user, s.mysql_pwd, s.console_domain, s.console_user, s.console_pwd')
                    -> where($field, 'like', '%'.trim($cnt).'%')
                    -> select();
                break;
            case 'notice':
                $table = 'web_notices';
                //$field = 'notice';
                $field = 'domain';
                $col[0] = [
                    'id' => '备忘信息ID',
                    'domain' => '网站域名',
                    'notice' => '备忘内容',
                    'notice_at' => '添加时间'
                ];
                $res = Db::table($table)
                    -> alias('n')
                    -> join('web_main m','n.domain_id = m.domain_id')
                    -> field('n.id, m.domain, n.notice, n.notice_at')
                    -> where($field, 'like', '%'.trim($cnt).'%')
                    -> select();
                break;
            case 'seo':
                $table = 'web_seo';
                //$field = 'seo_keywords';
                $field = 'domain';
                $col[0] = [
                    'seo_id' => 'ID',
                    'domain' => '网站域名',
                    'seo_keywords' => '网站关键词'
                ];
                $res = Db::table($table)
                    -> alias('s')
                    -> join('web_main m','s.domain_id = m.domain_id')
                    -> field('s.seo_id, m.domain, s.seo_keywords')
                    -> where($field, 'like', '%'.trim($cnt).'%')
                    -> select();
                break;
            default: break;
        }

        $resNum = count($res);
        $rowData[] = ['numRow' => $resNum];

        for ($i = 0; $i < $resNum;$i++) {
            $res[$i][$field] = str_replace($cnt,'<span class="red">'.$cnt.'</span>',$res[$i][$field]);

            if (isset($res[$i]['web_lrat'])){
                $res[$i]['web_lrat'] = date('Y-m-d H:i:s',$res[$i]['web_lrat']);
            }
            if (isset($res[$i]['web_srat'])){
                $res[$i]['web_srat'] = date('Y-m-d H:i:s',$res[$i]['web_srat']);
            }
            if (isset($res[$i]['domain_rat'])){
                $res[$i]['domain_rat'] = date('Y-m-d H:i:s',$res[$i]['domain_rat']);
            }
            if (isset($res[$i]['agent_rat'])){
                $res[$i]['agent_rat'] = date('Y-m-d H:i:s',$res[$i]['agent_rat']);
            }
            if (isset($res[$i]['notice_at'])){
                $res[$i]['notice_at'] = date('Y-m-d H:i:s',$res[$i]['notice_at']);
            }

        }

        return array_merge($col, $res, $rowData);

    }

    //批量导入数据：update or insert
    public static function dataImport($data)
    {
        if (!is_array($data)) return false;
        $res['allNum'] = 0;
        $res['flgNum'] = 0;
        $mainData = [];
        $seoData = [];
        $serverData = [];

        foreach ($data as $key => $val) {
            $res['allNum'] += 1;
            $domainID = Db::table('web_main')
                ->where('domain',trim($val['domain']))
                ->value('domain_id');

            //数据分表筛选
            $obj = (new self());
            $mFiled = $obj -> getColumn('web_main');
            $sFiled = $obj -> getColumn('web_seo');
            $svFiled = $obj -> getColumn('web_server');

            foreach ($val as $k => $item) {

                if (isset($mFiled[$k])){
                    $mainData[$k] = $item;
                    continue;
                }
                if (isset($sFiled[$k])){
                    $seoData[$k] = $item;
                    continue;
                }
                if (isset($svFiled[$k])){
                    $serverData[$k] = $item;
                    continue;
                }
            }
            Db::startTrans();
            try{
                //insert or update

                if (!$domainID) {

                    if (count($mainData) > 0) {
                        if (isset($mainData['web_lrat'])){
                            $mainData['web_lrat'] = strtotime($mainData['web_lrat']);
                        }
                        if (isset($mainData['web_srat'])){
                            $mainData['web_srat'] = strtotime($mainData['web_srat']);
                        }
                        if (isset($mainData['domain_rat'])){
                            $mainData['domain_rat'] = strtotime($mainData['domain_rat']);
                        } else {
                            $mainData['domain_rat'] = time();
                        }
                        if (isset($mainData['agent_rat'])){
                            $mainData['agent_rat'] = strtotime($mainData['agent_rat']);
                        }
                        $newDomainID = Db::name('main')
                            -> insertGetId($mainData);
                    } else {
                        return false;
                    }

                    if (count($seoData) > 0) {
                        $seoData['domain_id'] = $newDomainID;
                        Db::name('seo')
                            -> insert($seoData);
                    }

                    if (count($serverData) > 0) {
                        $serverData['domain_id'] = $newDomainID;
                        Db::name('server')
                            -> insert($serverData);
                    }

                } else {
                    //update
                    if (count($mainData) > 0){
                        Db::name('main')
                            -> where('domain_id', $domainID)
                            -> update($mainData);
                    }

                    if (count($serverData) > 0){
                        //确认server表中是否有数据
                        $serverID = Db::name('server')
                            -> where('domain_id',$domainID)
                            -> value('server_id');
                        if ($serverID) {
                            $serverUpdata = Db::name('server')
                                -> where('server_id', $serverID)
                                -> update($serverData);
                        } else {
                            $serverData['domain_id'] = $domainID;
                            Db::name('server')
                                -> insert($serverData);
                        }

                    }

                    if (count($seoData) > 0) {
                        //确认seo表中是否有数据
                        $seoID = Db::name('seo')
                            -> where('domain_id',$domainID)
                            -> value('seo_id');
                        if ($seoID) {
                            $seoUpdata = Db::name('seo')
                                -> where('seo_id', $seoID)
                                -> update($seoData);
                        } else {
                            $seoData['domain_id'] = $domainID;
                            Db::name('seo')
                                -> insert($seoData);
                        }

                    }
                }


                $res['flgNum'] += 1;
                if (isset($serverUpdata) && $serverUpdata == 0){
                    $res['flgNum'] -= 1;
                }
                if (isset($seoUpdata) && $seoUpdata == 0){
                    $res['flgNum'] -= 1;
                }
                Db::commit();
            }catch (\Exception $e){
                echo $e->getMessage();
                exit();
                Db::rollback();
            }

            $mainData = [];
            $seoData = [];
            $serverData = [];

        }

        return $res;

    }

    public static function importFiled($table)
    {
        if (!is_array($table)) return false;

        $tabNum = count($table);
        $column = [];
        $objSelf = new self();
        for ($i = 0; $i < $tabNum; $i++) {
            $tmpTab = 'web_'.trim($table[$i]);
            $tmpCol = $objSelf -> getColumn($tmpTab);

            if ($tmpCol) {
                array_push($column,$tmpCol);
            }else{
                continue;
            }

        }

        return $column;
    }
}