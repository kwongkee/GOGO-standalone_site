<?php
namespace app\index\model;

use think\Model;
use think\Db;
use think\Cache;

class WebsiteBasic extends Model
{
    protected $table = 'ims_website_basic'; // 指定表名
    
    // 检查网站是否存在（带缓存）
    public function existsByCompanyId($company_id, $company_type)
    {
        if (!is_numeric($company_id)) {
            return false;
        }
        // $cacheKey = 'website_basic_exists_' . $company_id;
        // $exists = Cache::get($cacheKey);
        // if ($exists === false) {
            $exists = $this->where(['company_id' => $company_id, 'company_type' => $company_type])->count();
        //     Cache::set($cacheKey, $exists, 3600);
        // }
        return $exists;
    }
    
    // 获取网站基本信息（批量字段）
    public function getByCompanyId($company_id, $company_type, $lang = 'zh')
    {
        $data = $this->where(['company_id' => $company_id, 'company_type' => $company_type])
                    ->find();
        if ($data) {
            // $data['domain'] = json_decode($data['domain'], true)[$lang] ?? $data['domain'];
            // 添加其他JSON处理
            if(isset($data['name'])){
                $data['name'] = json_decode($data['name'],true);
            }
            if(isset($data['desc'])) {
                $data['desc'] = json_decode($data['desc'], true);
            }
            if(isset($data['keywords'])) {
                $data['keywords'] = json_decode($data['keywords'], true);
            }
            if(isset($data['copyright'])) {
                $data['copyright'] = json_decode($data['copyright'], true);
            }
        }
        return $data;
    }
}