# WordPress Steam Games Display

一个简洁实用的WordPress插件，用于在您的网站上展示Steam游戏库和游玩时间。

![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.0+-green.svg)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

## ✨ 功能特点

- 🎮 **自动获取游戏数据** - 从Steam API获取您的游戏库
- 📊 **智能排序** - 按游戏时长自动排序，显示最常玩的游戏
- 📱 **移动友好** - 针对移动设备优化的布局

## 📸 预览效果

游戏以卡片形式展示，包含：
- 游戏封面图片
- 游戏名称
- 游玩时间统计

## 🚀 快速开始

### 安装方法

1. **下载插件文件**
   ```
   git clone https://github.com/PioneerZHA/wordpress-steam-games-display.git
   ```

2. **上传到WordPress**
   - 将文件夹重命名为 `steam-games-display`
   - 上传到 `wp-content/plugins/` 目录

3. **激活插件**
   - 在WordPress后台进入 **插件** → **已安装插件**
   - 找到 "Steam游戏时长展示" 并点击激活

### 配置设置

1. **获取Steam API密钥**
   - 访问 [Steam API Key](https://steamcommunity.com/dev/apikey)
   - 登录Steam账号并申请API密钥

2. **获取Steam ID**
   - 访问 [SteamID.io](https://steamid.io/)

3. **配置插件**
   - 进入 **设置** → **Steam游戏设置**
   - 填入API密钥和Steam ID

4. **设置Steam隐私**
   - 确保您的Steam个人资料设为 **公开**
   - 游戏详情也需要设为 **公开**

### 使用方法

在文章或页面中添加短代码：
```
[steam_games]
```

## 🔧 系统要求

- **WordPress:** 5.0 或更高版本
- **PHP:** 7.0 或更高版本  
- **网络:** 能够访问Steam API
- **Steam账号:** 需要公开的个人资料设置

## 🛠️ 故障排除

### 常见问题

**Q: 显示"加载中"或"加载失败"？**
- 检查Steam API密钥和Steam ID是否正确
- 确认Steam个人资料设为公开
- 使用后台的"测试API连接"功能诊断问题

**Q: 游戏图片显示不出来？**
- 插件会自动尝试多个Steam CDN源
- 如果所有源都失败，会显示包含游戏名称的占位图

### 调试方法

1. 在WordPress后台使用 **"测试Steam API连接"** 功能
2. 检查浏览器控制台的错误信息
3. 确认网络环境可以访问Steam API

## 📄 许可证

本项目采用 MIT 许可证

---

⭐ 如果这个插件对您有帮助，请给个星标支持一下！
