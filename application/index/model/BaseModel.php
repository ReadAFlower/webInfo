<?php
namespace app\index\model;

use think\Db;
use think\Model;

class BaseModel extends Model
{

    public function getColumn($table= '')
    {
        $table = !empty($table) ? $table : $this -> table;

        $res = Db::query('SHOW FULL COLUMNS FROM '.$table);
        $len = count($res);
        $col = [];
        for ($i=0; $i < $len; $i++) {
            $col[$res[$i]['Field']] = $res[$i]['Comment'];
        }
        return $col;
    }

    public function getBaseInfo($pageNow = 1, $pageSize = 12, $table= '')
    {
        if (empty($table)){
            $table = $this -> table;
        }
        $table = !empty($table) ? $table : $this -> table;
        $pageNow = intval($pageNow) > 0 ? intval($pageNow) : 1;
        $pageSize = intval($pageSize) > 0 ? intval($pageSize) : 12;

        //$Info = Db::query('SELECT * FROM '.$table);
        $offset = ($pageNow-1) * $pageSize;
        $Info = Db::table($table) -> limit($offset,$pageSize) -> select();

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
                    $Info[$i][$key] = '暂无';
                }
            }
        }

        return $Info;
    }

    public function del($domainID, $field)
    {
        $domainID = intval($domainID);
        $checkID = self::where('domain_id',$domainID)->value($field);

        if ($checkID) {
            return self::destroy(['domain_id' => $domainID]);
        }

        return true;
    }

    public static function datChange($data, $domainID, $table = 'main_server_seo')
    {
        $check = Db::table($table) -> where('domain_id',$domainID) -> find();
        if (!$check) {
            $data['domain_id'] = $domainID;
           $resID = Db::table($table) -> insertGetId($data);
           return $resID;
        }

        $res = Db::table($table)->where('domain_id',$domainID)->update($data);
        return $res;
    }


    public static function search($type, $cnt)
    {
        switch ($type) {
            case 'main':
                $table = 'web_main';
                $field = 'domain';
                $col[0] = (new self()) -> getColumn($table);
                $res = Db::table($table)
                    -> where($field, 'like', '%'.trim($cnt).'%')
                    -> select();
                break;
            case 'server':
                $table = 'web_server';
                $field = 'ftp_ip';
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
                    -> select();
                break;
            case 'notice':
                $table = 'web_notices';
                $field = 'notice';
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
                    -> select();
                break;
            case 'seo':
                $table = 'web_seo';
                $field = 'seo_keywords';
                $col[0] = [
                    'seo_id' => 'ID',
                    'domain' => '网站域名',
                    'seo_keywords' => '网站关键词'
                ];
                $res = Db::table($table)
                    -> alias('s')
                    -> join('web_main m','s.domain_id = m.domain_id')
                    -> field('s.seo_id, m.domain, s.seo_keywords')
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
}