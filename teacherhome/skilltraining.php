<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>하이터치 스킬 마스터링 포탈</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.6;
        }
        
        /* 헤더 스타일 */
        header {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .logo-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
        }
        
        .logo-text p {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .stats {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .stat-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: #3b82f6;
        }
        
        /* 탭 네비게이션 */
        .tab-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 72px;
            z-index: 90;
        }
        
        .tab-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            gap: 2rem;
        }
        
        .tab-button {
            padding: 1rem 0;
            border: none;
            background: none;
            font-size: 1rem;
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .tab-button:hover {
            color: #374151;
        }
        
        .tab-button.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }
        
        /* 메인 콘텐츠 */
        .main-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* 그리드 레이아웃 */
        .grid-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        @media (max-width: 1024px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }
        
        /* 카드 스타일 */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        /* 인트로 배너 */
        .intro-banner {
            background: linear-gradient(135deg, #dbeafe 0%, #e9d5ff 100%);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .intro-banner h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        
        /* 카테고리 아이템 */
        .category-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
            transition: box-shadow 0.3s;
        }
        
        .category-item:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .category-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(to right, #f9fafb, #f3f4f6);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-header:hover {
            background: linear-gradient(to right, #f3f4f6, #e5e7eb);
        }
        
        .category-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .category-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .category-text h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .category-text p {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .expand-icon {
            transition: transform 0.3s;
        }
        
        .category-item.expanded .expand-icon {
            transform: rotate(90deg);
        }
        
        .category-content {
            display: none;
            padding: 1.5rem;
            background: white;
        }
        
        .category-item.expanded .category-content {
            display: block;
        }
        
        /* 페르소나 그리드 */
        .persona-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .persona-card {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .persona-card:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        
        .persona-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .persona-behaviors {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .persona-behaviors li {
            margin-left: 1rem;
            margin-bottom: 0.25rem;
        }
        
        /* 상세 가이드 스타일 */
        .detailed-guide {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .guide-header {
            margin-bottom: 2rem;
        }
        
        .guide-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .guide-section {
            margin-bottom: 1.5rem;
        }
        
        .guide-section h4 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .behavior-box {
            background: #f3e8ff;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .do-box {
            background: #d1fae5;
        }
        
        .dont-box {
            background: #fee2e2;
        }
        
        .tip-box {
            background: linear-gradient(to right, #dbeafe, #e0e7ff);
            border-radius: 8px;
            padding: 1.5rem;
        }
        
        .guide-list {
            list-style: none;
        }
        
        .guide-list li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }
        
        .list-marker {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            margin-right: 0.75rem;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .do-marker {
            background: #10b981;
            color: white;
        }
        
        .dont-marker {
            background: #ef4444;
            color: white;
        }
        
        /* 체크리스트 */
        .checklist {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .checklist label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }
        
        .checklist input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        
        /* 진도 통계 */
        .progress-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .progress-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #10b981, #059669);
            border-radius: 6px;
            transition: width 0.5s ease;
        }
        
        .progress-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            text-align: center;
        }
        
        .stat-box {
            padding: 0.75rem;
            border-radius: 8px;
        }
        
        .stat-box.blue {
            background: #dbeafe;
        }
        
        .stat-box.purple {
            background: #e9d5ff;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .stat-desc {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        /* 교수 스킬 그리드 */
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .skill-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            transition: box-shadow 0.3s;
        }
        
        .skill-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .skill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .skill-icon {
            font-size: 2rem;
        }
        
        .skill-level {
            display: flex;
            gap: 0.25rem;
        }
        
        .star {
            color: #fbbf24;
        }
        
        .star.empty {
            color: #e5e7eb;
        }
        
        /* 버튼 스타일 */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        /* 추천 카드 */
        .recommendation-card {
            background: linear-gradient(135deg, #e9d5ff 0%, #fce7f3 100%);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* 팁 카드 */
        .tips-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        
        .tip-item {
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 0.75rem;
        }
        
        .tip-item.blue {
            background: #dbeafe;
        }
        
        .tip-item.green {
            background: #d1fae5;
        }
        
        .tip-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .tip-content {
            font-size: 0.875rem;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <!-- 헤더 -->
    <header>
        <div class="header-content">
            <div class="logo-section">
                <div class="logo">🧠</div>
                <div class="logo-text">
                    <h1>Mathking 교사 역량 개발</h1>
                    <p>학생 페르소나별 맞춤 지도법 마스터</p>
                </div>
            </div>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-label">학습 시간</div>
                    <div class="stat-value">42시간</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">마스터 레벨</div>
                    <div class="stat-value">⭐ Lv.3</div>
                </div>
            </div>
        </div>
    </header>

    <!-- 탭 네비게이션 -->
    <nav class="tab-nav">
        <div class="tab-container">
            <button class="tab-button active" onclick="showTab('persona')">
                👤 페르소나 학습
            </button>
            <button class="tab-button" onclick="showTab('skills')">
                ⚡ 교수 스킬
            </button>
            <button class="tab-button" onclick="showTab('practice')">
                🎯 실전 연습
            </button>
            <button class="tab-button" onclick="showTab('resources')">
                📚 자료실
            </button>
        </div>
    </nav>

    <!-- 메인 콘텐츠 -->
    <main class="main-content">
        <!-- 페르소나 학습 탭 -->
        <div id="persona-tab" class="tab-content active">
            <div class="grid-layout">
                <div>
                    <div id="persona-overview">
                        <div class="intro-banner">
                            <h2>Mathking 페르소나별 지도법</h2>
                            <p>각 기능별로 나타나는 학생들의 행동 패턴을 이해하고, 효과적인 지도 방법을 학습하세요.</p>
                        </div>
                        
                        <!-- 카테고리 목록 -->
                        <div id="category-list">
                            <!-- JavaScript로 동적 생성 -->
                        </div>
                    </div>
                    
                    <div id="persona-detail" style="display: none;">
                        <!-- JavaScript로 동적 생성 -->
                    </div>
                </div>
                
                <div>
                    <!-- 진도 통계 -->
                    <div class="progress-card">
                        <div class="progress-header">
                            <span>📈</span>
                            <h3 style="font-size: 1.125rem; font-weight: 600;">학습 진도</h3>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                <span style="color: #6b7280;">전체 진행률</span>
                                <span style="font-weight: 500;"><span id="completed-count">0</span>/<span id="total-count">20</span> 완료</span>
                            </div>
                            <div class="progress-bar">
                                <div id="progress-fill" class="progress-fill" style="width: 0%;"></div>
                            </div>
                        </div>
                        <div class="progress-stats">
                            <div class="stat-box blue">
                                <div class="stat-number" id="completed-number">0</div>
                                <div class="stat-desc">학습 완료</div>
                            </div>
                            <div class="stat-box purple">
                                <div class="stat-number" id="remaining-number">20</div>
                                <div class="stat-desc">남은 학습</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 오늘의 학습 추천 -->
                    <div class="recommendation-card">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <span>💡</span>
                            <span style="font-size: 1.125rem; font-weight: 600;">오늘의 학습 추천</span>
                        </h3>
                        <p style="font-size: 0.875rem; margin-bottom: 1rem;">
                            "목표지향형" 학생의 특성을 이해하고 효과적인 지도법을 익혀보세요.
                        </p>
                        <button class="btn btn-primary" style="background: #8b5cf6;">학습 시작하기</button>
                    </div>
                    
                    <!-- 빠른 팁 -->
                    <div class="tips-card">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <span>💬</span>
                            <span style="font-size: 1.125rem; font-weight: 600;">빠른 팁</span>
                        </h3>
                        <div class="tip-item blue">
                            <div class="tip-title">페르소나 파악이 먼저!</div>
                            <div class="tip-content">학생의 행동 패턴을 2주간 관찰한 후 페르소나를 결정하세요.</div>
                        </div>
                        <div class="tip-item green">
                            <div class="tip-title">점진적 접근</div>
                            <div class="tip-content">한 번에 모든 것을 바꾸려 하지 말고 작은 변화부터 시작하세요.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 교수 스킬 탭 -->
        <div id="skills-tab" class="tab-content">
            <div class="skills-grid">
            <!-- JavaScript로 동적 생성 -->
            </div>
        </div>

        <!-- 실전 연습 탭 -->
        <div id="practice-tab" class="tab-content">
            <div class="card" style="text-align: center; padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🏆</div>
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">실전 시나리오 연습</h2>
                <p style="color: #6b7280; margin-bottom: 2rem;">
                    실제 학생 상황을 바탕으로 한 시나리오를 통해 대응 능력을 향상시키세요.
                </p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; max-width: 600px; margin: 0 auto;">
                    <button class="card" style="border: 2px solid #3b82f6; cursor: pointer;">
                        <h3 style="color: #3b82f6; margin-bottom: 0.5rem;">초급</h3>
                        <p style="font-size: 0.875rem; color: #6b7280;">기본 대응 연습</p>
                    </button>
                    <button class="card" style="border: 2px solid #8b5cf6; cursor: pointer;">
                        <h3 style="color: #8b5cf6; margin-bottom: 0.5rem;">중급</h3>
                        <p style="font-size: 0.875rem; color: #6b7280;">복합 상황 대처</p>
                    </button>
                    <button class="card" style="border: 2px solid #ef4444; cursor: pointer;">
                        <h3 style="color: #ef4444; margin-bottom: 0.5rem;">고급</h3>
                        <p style="font-size: 0.875rem; color: #6b7280;">긴급 상황 해결</p>
                    </button>
                </div>
            </div>
        </div>

        <!-- 자료실 탭 -->
        <div id="resources-tab" class="tab-content">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem;">교수법 가이드</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">Mathking 시스템 완벽 활용법</span>
                            <span>→</span>
                        </div>
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">페르소나별 상담 매뉴얼</span>
                            <span>→</span>
                        </div>
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">효과적인 피드백 전달법</span>
                            <span>→</span>
                        </div>
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">학부모 상담 가이드</span>
                            <span>→</span>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem;">템플릿 & 도구</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">학생 관찰 기록지</span>
                            <span>→</span>
                        </div>
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">주간 학습 계획표</span>
                            <span>→</span>
                        </div>
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">페르소나 진단 체크리스트</span>
                            <span>→</span>
                        </div>
                        <div class="card" style="padding: 0.75rem; background: #f9fafb; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 500;">상담 일지 템플릿</span>
                            <span>→</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Mathking 카테고리 데이터
        const mathkingCategories = {
            "학습관리 페이지": {
                icon: "📋",
                description: "학습 전반을 관리하는 메인 페이지 활용 지도법",
                personas: [
                    {
                        id: "goal-oriented",
                        name: "목표지향형",
                        behaviors: ["진입 후 첫 클릭이 목표 영역", "체류 시간 짧음", "목표 확인 후 빠른 이동"],
                        teachingStrategy: {
                            do: [
                                "목표 달성 현황을 시각적으로 명확히 제시",
                                "단기/장기 목표를 구체적 숫자로 표현",
                                "달성 가능한 일일 목표 세분화"
                            ],
                            dont: [
                                "목표 없이 막연한 학습 권유",
                                "추상적이고 모호한 목표 설정",
                                "과도하게 높은 목표 제시"
                            ],
                            tips: "이 유형은 명확한 목표가 있을 때 동기부여가 극대화됩니다. 매일 달성 가능한 작은 목표부터 시작하세요."
                        }
                    },
                    {
                        id: "immediate-questioner",
                        name: "즉시 질문형",
                        behaviors: ["도움 요청 아이콘 즉시 클릭", "질문 입력까지 시간 빠름", "학습보다 소통 우선"],
                        teachingStrategy: {
                            do: [
                                "질문하기 전 스스로 생각해볼 시간 제공",
                                "질문 유형별 해결 프로세스 안내",
                                "자기주도적 문제해결 능력 단계적 육성"
                            ],
                            dont: [
                                "질문을 무시하거나 차단",
                                "즉각적인 답변만 제공",
                                "질문하는 것을 부정적으로 평가"
                            ],
                            tips: "질문형 학생의 호기심은 소중한 자산입니다. 질문을 통해 스스로 답을 찾아가는 과정을 설계하세요."
                        }
                    },
                    {
                        id: "explorer",
                        name: "탐색형",
                        behaviors: ["좌측 메뉴 클릭 수 > 3", "다른 페이지로 이동 빈도 높음", "탐색적 행동 패턴"],
                        teachingStrategy: {
                            do: [
                                "체계적인 학습 경로 가이드 제공",
                                "탐색 후 핵심 내용 정리 시간 확보",
                                "다양한 기능의 효과적 활용법 안내"
                            ],
                            dont: [
                                "탐색 자체를 부정적으로 평가",
                                "한 곳에만 머물도록 강요",
                                "탐색 시간을 낭비로 간주"
                            ],
                            tips: "탐색형의 호기심을 학습 깊이로 전환시키는 것이 핵심입니다. 탐색 후 반드시 정리하는 습관을 만들어주세요."
                        }
                    }
                ]
            },
            "내공부방": {
                icon: "📚",
                description: "개인 학습 공간 관리 및 오답노트 활용 지도법",
                personas: [
                    {
                        id: "self-driven",
                        name: "자기주도 관리형",
                        behaviors: ["과목 관리 자주 변경", "오답노트 진입 빈도 높음", "알림 즉각 처리"],
                        teachingStrategy: {
                            do: [
                                "자율성을 존중하며 더 높은 목표 제시",
                                "효율적인 오답 관리 고급 기법 전수",
                                "학습 데이터 기반 심화 분석 지도"
                            ],
                            dont: [
                                "과도한 개입이나 지시",
                                "기본적인 관리 방법만 반복",
                                "자율성을 제한하는 규칙 부과"
                            ],
                            tips: "이미 좋은 습관을 가진 학생입니다. 더 높은 수준의 학습 전략을 제시하여 성장을 돕습니다."
                        }
                    },
                    {
                        id: "avoidance",
                        name: "오답 회피형",
                        behaviors: ["오답노트 접근 횟수 극히 적음", "머문 시간 매우 짧음", "오답 회피 성향"],
                        teachingStrategy: {
                            do: [
                                "오답을 성장의 기회로 재정의",
                                "실패에 대한 두려움 완화 활동",
                                "작은 성공 경험부터 축적"
                            ],
                            dont: [
                                "오답을 부정적으로 강조",
                                "실패에 대한 처벌이나 비난",
                                "강제로 오답노트 작성 요구"
                            ],
                            tips: "오답에 대한 부정적 인식을 바꾸는 것이 첫 단계입니다. 심리적 안전감을 제공하세요."
                        }
                    }
                ]
            },
            "공부결과": {
                icon: "📊",
                description: "학습 데이터 분석 및 성과 확인 지도법",
                personas: [
                    {
                        id: "result-oriented",
                        name: "성과확인형",
                        behaviors: ["첫 클릭 Progress Bar", "성과 관련 데이터 집중 탐색", "목표 달성률 중시"],
                        teachingStrategy: {
                            do: [
                                "구체적이고 측정 가능한 목표 설정",
                                "성과와 과정의 균형있는 평가",
                                "데이터 기반 개선 전략 수립"
                            ],
                            dont: [
                                "결과만을 강조하는 피드백",
                                "과정의 중요성 무시",
                                "타인과의 비교 위주 평가"
                            ],
                            tips: "성과 지향성을 긍정적으로 활용하되, 과정의 가치도 함께 인식하도록 도와주세요."
                        }
                    },
                    {
                        id: "data-avoider",
                        name: "데이터회피형",
                        behaviors: ["진입 후 매우 짧은 시간 체류", "데이터 클릭 없음", "구체적 지표 회피"],
                        teachingStrategy: {
                            do: [
                                "데이터의 긍정적 활용법 안내",
                                "간단한 지표부터 단계적 접근",
                                "데이터를 통한 성장 스토리 공유"
                            ],
                            dont: [
                                "복잡한 데이터 분석 강요",
                                "부정적 결과 강조",
                                "데이터 없이는 학습 불가능하다고 압박"
                            ],
                            tips: "데이터에 대한 부담을 줄이고, 자신의 성장을 확인하는 도구로 인식하도록 도와주세요."
                        }
                    }
                ]
            },
            "포모도로": {
                icon: "⏰",
                description: "집중력 관리 및 학습 기록 습관 형성 지도법",
                personas: [
                    {
                        id: "deep-focus",
                        name: "집중몰입형",
                        behaviors: ["타이머 사용 규칙적", "성찰 입력 빈도 높음", "중단 횟수 적음"],
                        teachingStrategy: {
                            do: [
                                "집중력 유지 고급 기법 전수",
                                "몰입 경험 확대 방안 제시",
                                "성찰의 깊이 향상 지도"
                            ],
                            dont: [
                                "집중 시간 과도하게 연장",
                                "휴식의 중요성 간과",
                                "완벽한 집중만 요구"
                            ],
                            tips: "이미 훌륭한 집중력을 가지고 있습니다. 지속가능한 학습 루틴을 만들어가도록 도와주세요."
                        }
                    }
                ]
            },
            "목표설정": {
                icon: "🎯",
                description: "효과적인 학습 목표 수립 및 관리 지도법",
                personas: [
                    {
                        id: "plan-faithful",
                        name: "계획충실형",
                        behaviors: ["목표 설정 및 수정 빈도 높음", "강좌 연동 기능 자주 활용", "상세한 계획 수립"],
                        teachingStrategy: {
                            do: [
                                "계획의 유연성 향상 지도",
                                "우선순위 설정 기법 전수",
                                "계획과 실행의 균형 유지"
                            ],
                            dont: [
                                "과도하게 세밀한 계획 요구",
                                "계획 변경을 실패로 간주",
                                "경직된 실행만 강조"
                            ],
                            tips: "계획 수립 능력이 뛰어난 학생입니다. 실행력과 유연성을 함께 기를 수 있도록 지도하세요."
                        }
                    }
                ]
            }
        };

        // 교수 스킬 데이터
        const teachingSkills = [
            {
                title: "상황별 대응 능력",
                icon: "⚠️",
                skills: ["즉각적 피드백", "오답 지도법", "동기부여 전략"],
                level: 3,
                color: "#f97316"
            },
            {
                title: "설명 능력 향상",
                icon: "💬",
                skills: ["15초 핵심 설명", "시각화 기법", "단계별 설명법"],
                level: 2,
                color: "#3b82f6"
            },
            {
                title: "데이터 활용 지도",
                icon: "📊",
                skills: ["학습 분석", "맞춤형 처방", "성과 측정"],
                level: 4,
                color: "#10b981"
            },
            {
                title: "집중력 관리",
                icon: "⏰",
                skills: ["포모도로 활용", "주의력 향상", "학습 리듬"],
                level: 2,
                color: "#8b5cf6"
            },
            {
                title: "목표 설정 코칭",
                icon: "🎯",
                skills: ["SMART 목표", "동기 부여", "진도 관리"],
                level: 3,
                color: "#ef4444"
            },
            {
                title: "메타인지 개발",
                icon: "🧠",
                skills: ["자기 성찰", "학습 전략", "사고력 향상"],
                level: 1,
                color: "#6366f1"
            }
        ];

        let currentPersona = null;
        let completedLessons = [];

        // 탭 전환 함수
        function showTab(tabId) {
            // 모든 탭 콘텐츠 숨기기
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // 모든 탭 버튼 비활성화
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // 선택된 탭 표시
            document.getElementById(tabId + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            // 교수 스킬 탭이면 스킬 카드 생성
            if (tabId === 'skills') {
                renderSkills();
            }
        }

        // 카테고리 목록 렌더링
        function renderCategories() {
            const categoryList = document.getElementById('category-list');
            categoryList.innerHTML = '';
            
            Object.entries(mathkingCategories).forEach(([category, data]) => {
                const categoryItem = document.createElement('div');
                categoryItem.className = 'category-item';
                categoryItem.innerHTML = `
                    <div class="category-header" onclick="toggleCategory('${category}')">
                        <div class="category-info">
                            <div class="category-icon">${data.icon}</div>
                            <div class="category-text">
                                <h3>${category}</h3>
                                <p>${data.description}</p>
                            </div>
                        </div>
                        <span class="expand-icon">▶</span>
                    </div>
                    <div class="category-content">
                        <div class="persona-grid">
                            ${data.personas.map(persona => `
                                <div class="persona-card" onclick="showPersonaDetail('${category}', '${persona.id}')">
                                    <h4 class="persona-name">${persona.name}</h4>
                                    <ul class="persona-behaviors">
                                        ${persona.behaviors.slice(0, 2).map(behavior => `
                                            <li>• ${behavior}</li>
                                        `).join('')}
                                    </ul>
                                    <div style="margin-top: 0.75rem; display: flex; align-items: center; color: #3b82f6; font-size: 0.75rem;">
                                        <span>자세히 보기</span>
                                        <span style="margin-left: 0.25rem;">→</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
                categoryList.appendChild(categoryItem);
            });
            
            updateProgress();
        }

        // 카테고리 토글
        function toggleCategory(category) {
            const categoryItems = document.querySelectorAll('.category-item');
            categoryItems.forEach(item => {
                const header = item.querySelector('.category-header');
                const categoryText = header.querySelector('.category-text h3').textContent;
                
                if (categoryText === category) {
                    item.classList.toggle('expanded');
                } else {
                    item.classList.remove('expanded');
                }
            });
        }

        // 페르소나 상세 표시
        function showPersonaDetail(category, personaId) {
            const persona = mathkingCategories[category].personas.find(p => p.id === personaId);
            currentPersona = { ...persona, category };
            
            document.getElementById('persona-overview').style.display = 'none';
            document.getElementById('persona-detail').style.display = 'block';
            
            const detailContainer = document.getElementById('persona-detail');
            detailContainer.innerHTML = `
                <div class="detailed-guide">
                    <div class="guide-header">
                        <h3>${persona.name} 학생 지도법</h3>
                        <p style="color: #6b7280;">${category} 영역</p>
                    </div>
                    
                    <div class="guide-section">
                        <h4><span>🧠</span> 주요 행동 특성</h4>
                        <div class="behavior-box">
                            <ul class="guide-list">
                                ${persona.behaviors.map(behavior => `
                                    <li>
                                        <span style="color: #8b5cf6; margin-right: 0.5rem;">✓</span>
                                        ${behavior}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                    
                    <div class="guide-section">
                        <h4><span>✅</span> 권장 지도 방법 (DO)</h4>
                        <div class="do-box">
                            <ul class="guide-list">
                                ${persona.teachingStrategy.do.map((item, idx) => `
                                    <li>
                                        <span class="list-marker do-marker">${idx + 1}</span>
                                        <span>${item}</span>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                    
                    <div class="guide-section">
                        <h4><span>⚠️</span> 피해야 할 지도 방법 (DON'T)</h4>
                        <div class="dont-box">
                            <ul class="guide-list">
                                ${persona.teachingStrategy.dont.map(item => `
                                    <li>
                                        <span class="list-marker dont-marker">✕</span>
                                        <span>${item}</span>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                    
                    <div class="tip-box">
                        <h4 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <span>💡</span> 핵심 지도 팁
                        </h4>
                        <p style="color: #374151; line-height: 1.8;">${persona.teachingStrategy.tips}</p>
                    </div>
                    
                    <div class="guide-section" style="margin-top: 1.5rem;">
                        <h4><span>✓</span> 실전 적용 체크리스트</h4>
                        <div class="checklist">
                            <label>
                                <input type="checkbox" onchange="updateChecklist()">
                                <span>학생의 행동 패턴을 정확히 파악했나요?</span>
                            </label>
                            <label>
                                <input type="checkbox" onchange="updateChecklist()">
                                <span>권장 지도 방법을 적용할 계획을 세웠나요?</span>
                            </label>
                            <label>
                                <input type="checkbox" onchange="updateChecklist()">
                                <span>피해야 할 방법을 인지하고 있나요?</span>
                            </label>
                            <label>
                                <input type="checkbox" onchange="updateChecklist()">
                                <span>학생과의 첫 상담 계획을 준비했나요?</span>
                            </label>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <button class="btn btn-primary" onclick="completeLesson()">학습 완료</button>
                        <button class="btn btn-secondary" onclick="backToOverview()">목록으로</button>
                    </div>
                </div>
            `;
        }

        // 체크리스트 업데이트
        function updateChecklist() {
            const checkboxes = document.querySelectorAll('.checklist input[type="checkbox"]');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            
            if (checkedCount === checkboxes.length) {
                document.querySelector('.btn-primary').style.background = '#10b981';
            }
        }

        // 학습 완료 처리
        function completeLesson() {
            if (currentPersona && !completedLessons.includes(currentPersona.id)) {
                completedLessons.push(currentPersona.id);
                updateProgress();
                alert('학습을 완료했습니다! 🎉');
                backToOverview();
            }
        }

        // 목록으로 돌아가기
        function backToOverview() {
            document.getElementById('persona-overview').style.display = 'block';
            document.getElementById('persona-detail').style.display = 'none';
            currentPersona = null;
        }

        // 진도 업데이트
        function updateProgress() {
            const totalPersonas = Object.values(mathkingCategories).reduce(
                (sum, cat) => sum + cat.personas.length, 0
            );
            const completedCount = completedLessons.length;
            const progressPercentage = (completedCount / totalPersonas) * 100;
            
            document.getElementById('completed-count').textContent = completedCount;
            document.getElementById('total-count').textContent = totalPersonas;
            document.getElementById('completed-number').textContent = completedCount;
            document.getElementById('remaining-number').textContent = totalPersonas - completedCount;
            document.getElementById('progress-fill').style.width = progressPercentage + '%';
        }

        // 교수 스킬 렌더링
        function renderSkills() {
            const skillsGrid = document.querySelector('.skills-grid');
            if (!skillsGrid) return;
            
            skillsGrid.innerHTML = '<a href="https://claude.ai/public/artifacts/258ab68a-d756-4a66-81ec-1fb59dfd52ad">좋은 수업의 서사는 좋은 설명의 서사</a>';
            
            teachingSkills.forEach(skill => {
                const skillCard = document.createElement('div');
                skillCard.className = 'skill-card';
                skillCard.innerHTML = `
                    <div class="skill-header">
                        <div class="skill-icon" style="color: ${skill.color};">${skill.icon}</div>
                        <div class="skill-level">
                            ${Array.from({length: 5}, (_, i) => 
                                `<span class="star ${i < skill.level ? '' : 'empty'}">★</span>`
                            ).join('')}
                        </div>
                    </div>
                    <h3 style="font-weight: 700; font-size: 1.125rem; margin-bottom: 0.75rem;">${skill.title}</h3>
                    <div style="margin-bottom: 1rem;">
                        ${skill.skills.map(item => `
                            <div style="display: flex; align-items: center; font-size: 0.875rem; color: #4b5563; margin-bottom: 0.5rem;">
                                <span style="color: #10b981; margin-right: 0.5rem;">✓</span>
                                ${item}
                            </div>
                        `).join('')}
                    </div>
                    <button class="btn btn-secondary">학습하기</button>
                `;
                skillsGrid.appendChild(skillCard);
            });
        }

        // 초기화
        document.addEventListener('DOMContentLoaded', function() {
            renderCategories();
        });
    </script>
</body>
</html>