# GOGO-standalone_site 架构文档

> **生成时间:** 2026-04-15  
> **技术栈:** ThinkPHP 5.0 / PHP 7.2  
> **服务端口:** 8003  
> **访问地址:** https://independent.gogo198.com  
> **数据来源:** 服务器真实代码扫描  

---

## 📊 项目概览

| 属性 | 值 |
|------|-----|
| **项目名称** | GOGO-standalone_site（GOGO独立站） |
| **技术框架** | ThinkPHP 5.0 |
| **PHP版本** | 7.2 |
| **数据库** | MySQL |
| **部署方式** | Git直接部署 |
| **CI/CD** | GitHub Actions（智能6阶段流程） |
| **路由数量** | 与Admin/AI共享相同路由结构 |
| **GitHub提交** | 273 次 |

---

## 📁 目录结构

```
independent.gogo198.com/
│
├── 📂 application/                    # ThinkPHP应用核心 ⭐
│   ├── 📂 index/
│   │   └── 📂 controller/             # 前台控制器（7个）
│   │       ├── Customer.php           # 客户管理
│   │       ├── Gather.php             # 数据采集
│   │       ├── Index.php              # 首页/信息展示
│   │       ├── Loggin.php             # 日志管理
│   │       ├── Main.php               # 主控制器/决策网 ⭐
│   │       ├── Merchant.php           # 商户管理
│   │       └── Shop.php               # 店铺管理
│   ├── 📂 api/
│   │   └── 📂 controller/             # API控制器
│   ├── config.php                     # 应用配置
│   ├── common.php                     # 公共函数库
│   ├── route.php                      # 路由配置
│   └── database.php                   # 数据库配置
│
├── 📂 public/                         # Web根目录（入口）
│   └── index.php                      # 入口文件
├── 📂 thinkphp/                       # ThinkPHP框架核心
├── 📂 vendor/                         # Composer依赖
├── 📂 extend/                         # 自定义扩展
├── 📂 runtime/                        # 运行时目录
│   ├── cache/                         # 框架缓存
│   └── log/                           # 运行日志
├── 📂 .well-known/                    # SSL/验证文件
│
├── 📄 think                           # 命令行工具
├── 📄 build.php                       # 构建脚本
├── 📄 composer.json                   # PHP依赖配置
├── 📄 composer.lock                   # 依赖版本锁定
├── 📄 .htaccess                       # Apache伪静态规则
├── 📄 LICENSE.txt                     # 许可证
├── 📄 README.md                       # 项目说明
├── 📄 CHANGELOG.md                   # 变更日志
├── 📄 MP_verify_UwFjMrSKelIbvktq.txt # 微信域名验证
└── 📄 QloJknuzQH.txt                  # 第三方验证文件
```

---

## 🎮 控制器详解

### 前台控制器 (`application/index/controller/`)

| 控制器 | 功能说明 | 备注 |
|--------|----------|------|
| **Main** | 主控制器/决策网模块 ⭐ | 首页路由 `/` 入口 |
| **Index** | 首页/信息展示/客户背景调查 | 同Admin/AI |
| **Merchant** | 商户管理 | 同Admin |
| **Customer** | 客户管理 | 同Admin |
| **Shop** | 店铺管理 | 同Admin |
| **Gather** | 数据采集管理 | 同Admin |
| **Loggin** | 系统日志管理 | 同Admin |

> **对比分析:**  
> - 有 `Main.php` 和 `Merchant.php`，无 `Member.php`/`Members.php`/`Memberc.php`/`Monitor.php`  
> - 独立站定位：主打决策网功能 + 商户展示，无会员体系和系统监控

---

## 🛤️ API路由文档（真实路由节选）

### 首页路由
| 方法 | 路由 | 控制器 | 说明 |
|------|------|--------|------|
| GET | `/` | `index/main/index` | 网站首页（决策网入口） |

### 决策网模块（Main控制器）
| 方法 | 路由 | 说明 |
|------|------|------|
| GET | `main/guide_page` | 列表页/代购商城页 |
| GET | `main/disease_detail` | 疾病详情 |
| GET | `main/production_list` | 当前国家生产商列表 |
| GET | `main/medical_detail` | 药品详情 |
| GET | `main/get_production` | AJAX获取厂商详情 |
| GET | `main/production_detail` | 厂商详情页 |
| GET | `main/detail` | 新闻/消息详情 |
| POST | `main/search_info` | 首页查询跳转 |
| ANY | `main/staff_reg` | 人员验证 |

### 信息展示模块（Index控制器）
| 方法 | 路由 | 说明 |
|------|------|------|
| ANY | `index/enterprise_news` | 购购动态 |
| ANY | `index/cross_news` | 跨境新闻 |
| ANY | `index/chooseMarket` | 选市场 |
| ANY | `index/customers` | 找客户 |
| ANY | `index/background_email` | 全球客户背景调查-邮箱 |
| ANY | `index/background_company` | 全球客户背景调查-企业 |
| ANY | `index/background_overseasreport` | 信用报告 |
| ANY | `index/KYBreport` | KYB合规报告 |
| ANY | `index/searchengine` | 搜索引擎获客 |
| ANY | `index/domainsearch` | 域名获客 |
| ANY | `index/findcustomers` | 海关数据获客 |
| ANY | `index/enterprise` | 社交媒体获客 |
| ANY | `index/account_manage` | 账户管理 |
| ANY | `index/merchant_reg` | 商户认证 |

---

## 🔧 核心功能模块

### 1. 决策网（Main控制器）⭐
独立站的核心功能，医药/贸易决策支持平台：
- 全球药品/疾病数据检索
- 生产商/厂商信息查询
- 代购商城入口

### 2. 全球客户背景调查（Index控制器）
- 邮箱/网站/企业信息查询
- KYB合规检查
- 海关数据、社交媒体、搜索引擎获客工具

### 3. 商户体系
- 商户信息展示
- 商户认证注册
- 店铺管理

### 4. 信息资讯
- 跨境电商新闻
- 企业动态
- 行业资讯

---

## 🔄 四个项目对比

| 功能模块 | Admin | shop | AI | standalone |
|---------|-------|------|----|-----------|
| 决策网(Main) | ✅ | ❌ | ❌ | ✅ |
| 会员管理 | ✅ | ✅ | ✅ | ❌ |
| 系统监控 | ✅ | ❌ | ❌ | ❌ |
| 商户管理 | ✅ | ✅ | ❌ | ✅ |
| 背景调查 | ✅ | ❌ | ✅ | ✅ |
| 电商功能 | ❌ | ✅ | ❌ | ❌ |

---

## 🛠️ 部署信息

| 项目 | 值 |
|------|-----|
| **服务器** | 阿里云 ECS 39.108.11.214 (CentOS 7) |
| **部署路径** | `/www/wwwroot/independent.gogo198.com/` |
| **访问地址** | https://independent.gogo198.com |
| **服务端口** | 8003 |
| **运行用户** | www |
| **备份目录** | `/opt/backups/gogo-standalone/` |
| **文件权限** | public: 755, runtime: 777 |

---

## 🔐 第三方集成

| 集成 | 文件 | 说明 |
|------|------|------|
| 微信域名验证 | `MP_verify_UwFjMrSKelIbvktq.txt` | 微信公众平台 |
| 第三方验证 | `QloJknuzQH.txt` | 第三方服务验证 |

---

## 📈 CI/CD 流程状态

| 阶段 | 状态 | 说明 |
|------|------|------|
| 代码审核 | ✅ 已配置 | SonarQube + PHP语法检查 |
| 架构文档生成 | ✅ 已配置 | 自动生成docs/ARCHITECTURE.md |
| 修复建议 | ✅ 已配置 | GitHub Issue自动创建 |
| 自动修复 | ✅ 已配置 | 创建修复PR |
| 部署 | ✅ 已配置 | SSH直接部署 |
| 邮件通知 | ✅ 已配置 | 发送至198@gogo198.net |

---

*由 GOGO CI/CD 基于服务器真实代码扫描生成 · 2026-04-15*
