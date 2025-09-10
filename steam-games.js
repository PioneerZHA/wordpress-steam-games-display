function loadSteamGamesFromWordPress() {
    // 显示加载状态
    showLoadingState();
    
    jQuery.ajax({
        url: steam_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'get_steam_games'
        },
        success: function(response) {
            if (response.success && response.data) {
                displayGames(response.data);
            } else {
                var errorMsg = response.data || '未知错误';
                showErrorState('获取游戏数据失败: ' + errorMsg);
                console.error('Steam API错误:', response);
            }
        },
        error: function(xhr, status, error) {
            var errorMsg = '网络请求失败: ' + error;
            if (xhr.responseText) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.data) {
                        errorMsg = response.data;
                    }
                } catch (e) {
                    errorMsg += ' (响应解析失败)';
                }
            }
            showErrorState(errorMsg);
            console.error('AJAX请求详情:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                readyState: xhr.readyState
            });
        }
    });
}

function showLoadingState() {
    var container = document.querySelector('.steam-games-grid');
    if (container) {
        container.innerHTML = '<div class="loading-state">正在加载游戏数据...</div>';
    }
}

function showErrorState(message) {
    var container = document.querySelector('.steam-games-grid');
    if (container) {
        container.innerHTML = '<div class="error-state">' + message + '</div>';
    }
}

function displayGames(games) {
    var container = document.querySelector('.steam-games-grid');
    if (!container) return;
    
    if (games.length === 0) {
        container.innerHTML = '<div class="error-state">未找到游戏数据，请确保Steam个人资料为公开状态</div>';
        return;
    }
    
    var html = '';
    
    // 只显示前8个游戏
    var displayGames = games.slice(0, 8);
    
    displayGames.forEach(function(game, index) {
        var hours = Math.round(game.playtime_forever / 60 * 10) / 10;
        var gameName = game.name || '未知游戏';
        
        // 图片源数组
        var imgSources = [
            'https://steamcdn-a.akamaihd.net/steam/apps/' + game.appid + '/header.jpg',
            'https://cdn.akamai.steamstatic.com/steam/apps/' + game.appid + '/header.jpg',
            'https://media.st.dl.bscstorage.net/steam/apps/' + game.appid + '/header.jpg'
        ];
        
        html += '<div class="game-card">';
        html += '<img src="' + imgSources[0] + '" alt="' + gameName + '" ';
        html += 'data-sources=\'' + JSON.stringify(imgSources) + '\' ';
        html += 'data-current-index="0" ';
        html += 'data-game-name="' + gameName + '" ';
        html += 'onerror="handleImageError(this)">';
        html += '<div class="game-info">';
        html += '<h4 class="game-title">' + gameName + '</h4>';
        html += '<p class="game-playtime">' + hours + ' 小时</p>';
        html += '</div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

function handleImageError(img) {
    var sources = JSON.parse(img.dataset.sources);
    var currentIndex = parseInt(img.dataset.currentIndex);
    var gameName = img.dataset.gameName;
    
    if (currentIndex < sources.length - 1) {
        // 尝试下一个图片源
        img.dataset.currentIndex = (currentIndex + 1).toString();
        img.src = sources[currentIndex + 1];
    } else {
        // 所有图片源都失败，显示占位图
        img.src = createPlaceholderImage(gameName);
        img.style.background = '#f8f9fa';
    }
}

function createPlaceholderImage(gameName) {
    var shortName = gameName.length > 15 ? gameName.substring(0, 15) + '...' : gameName;
    var encodedName = encodeURIComponent(shortName);
    
    return 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'460\' height=\'215\' style=\'background:%23f8f9fa\'%3E' +
           '%3Crect width=\'460\' height=\'215\' fill=\'%23f8f9fa\'/%3E' +
           '%3Ctext x=\'230\' y=\'100\' text-anchor=\'middle\' fill=\'%23495057\' font-size=\'18\' font-family=\'Arial, sans-serif\'%3E' + encodedName + '%3C/text%3E' +
           '%3Ctext x=\'230\' y=\'125\' text-anchor=\'middle\' fill=\'%236c757d\' font-size=\'14\' font-family=\'Arial, sans-serif\'%3ESteam游戏%3C/text%3E' +
           '%3C/svg%3E';
}