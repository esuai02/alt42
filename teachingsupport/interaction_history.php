<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI 수학 학습 이벤트 로그</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .refresh-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-left: auto;
        }

        .refresh-btn:hover {
            background: #2563eb;
        }

        .event-log {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .event-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s;
        }

        .event-item:hover {
            background: #f9fafb;
        }

        .event-item:last-child {
            border-bottom: none;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .status-complete { background: #10b981; }
        .status-question { background: #f59e0b; }
        .status-view { background: #6b7280; }
        .status-error { background: #ef4444; }
        .status-start { background: #3b82f6; }

        .event-content {
            flex: 1;
            min-width: 0;
        }

        .student-name {
            font-weight: 500;
            color: #1f2937;
            margin-right: 8px;
        }

        .class-badge {
            display: inline-block;
            background: #e5e7eb;
            color: #6b7280;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-right: 12px;
        }

        .event-description {
            color: #4b5563;
            font-size: 14px;
            margin-top: 2px;
        }

        .event-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
            flex-shrink: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .time-stamp {
            color: #9ca3af;
            white-space: nowrap;
        }

        .score-badge {
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .stats-bar {
            background: #f3f4f6;
            padding: 12px 20px;
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #6b7280;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #10b981;
            font-size: 14px;
            font-weight: 500;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .filter-tabs {
            display: flex;
            gap: 1px;
            background: #e5e7eb;
            border-radius: 6px;
            padding: 2px;
            margin-bottom: 16px;
        }

        .filter-tab {
            background: transparent;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: #6b7280;
            transition: all 0.2s;
        }

        .filter-tab.active {
            background: white;
            color: #1f2937;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">AI 수학 학습 실시간 로그</h1>
            <div class="live-indicator">
                <div class="live-dot"></div>
                <span>LIVE</span>
            </div>
            <button class="refresh-btn" onclick="refreshLog()">새로고침</button>
        </div>

        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterEvents('all')">전체</button>
            <button class="filter-tab" onclick="filterEvents('complete')">완료</button>
            <button class="filter-tab" onclick="filterEvents('question')">질문</button>
            <button class="filter-tab" onclick="filterEvents('error')">오류</button>
        </div>

        <div class="event-log">
            <div class="stats-bar">
                <div class="stat-item">📊 오늘 총 활동: <strong>47건</strong></div>
                <div class="stat-item">✅ 완료: <strong>32건</strong></div>
                <div class="stat-item">❓ 질문: <strong>8건</strong></div>
                <div class="stat-item">👥 활성 학생: <strong>23명</strong></div>
            </div>

            <div class="event-item">
                <div class="status-dot status-complete"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">김민준</span>
                        <span class="class-badge">3-1</span>
                        <span class="event-description">미분법 문제 3개 완료 (연속 정답)</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="score-badge">95점</span>
                    <span class="time-stamp">방금 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-question"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">박지호</span>
                        <span class="class-badge">3-2</span>
                        <span class="event-description">질문 등록: "삼각함수 덧셈정리 증명 과정이 이해가 안 되요"</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="time-stamp">2분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-view"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">이서연</span>
                        <span class="class-badge">3-1</span>
                        <span class="event-description">이차방정식 보충자료 확인 (2번째 재방문)</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="time-stamp">5분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-complete"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">정현우</span>
                        <span class="class-badge">3-3</span>
                        <span class="event-description">함수 그래프 그리기 문제 완료</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="score-badge">78점</span>
                    <span class="time-stamp">8분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-start"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">최유진</span>
                        <span class="class-badge">3-2</span>
                        <span class="event-description">학습 시작 (3일만에 재접속)</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="time-stamp">12분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-error"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">강민호</span>
                        <span class="class-badge">3-1</span>
                        <span class="event-description">연속 3회 오답 후 힌트 요청</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="time-stamp">15분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-complete"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">윤서아</span>
                        <span class="class-badge">3-3</span>
                        <span class="event-description">적분 기본공식 암기 테스트 완료</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="score-badge">100점</span>
                    <span class="time-stamp">18분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-question"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">홍길동</span>
                        <span class="class-badge">3-2</span>
                        <span class="event-description">질문 등록: "치환적분에서 변수 설정하는 방법을 모르겠어요"</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="time-stamp">22분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-view"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">김민주</span>
                        <span class="class-badge">3-1</span>
                        <span class="event-description">로그함수 성질 복습 자료 다운로드</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="time-stamp">25분 전</span>
                </div>
            </div>

            <div class="event-item">
                <div class="status-dot status-complete"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">이준혁</span>
                        <span class="class-badge">3-3</span>
                        <span class="event-description">수열의 극한 연습문제 5개 연속 정답</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="score-badge">92점</span>
                    <span class="time-stamp">28분 전</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshLog() {
            // 실제로는 서버에서 새 데이터를 가져와야 함
            const refreshBtn = document.querySelector('.refresh-btn');
            refreshBtn.textContent = '새로고침 중...';
            refreshBtn.disabled = true;
            
            setTimeout(() => {
                refreshBtn.textContent = '새로고침';
                refreshBtn.disabled = false;
                // 새로운 이벤트 추가 시뮬레이션
                addNewEvent();
            }, 1000);
        }

        function addNewEvent() {
            const eventLog = document.querySelector('.event-log');
            const statsBar = eventLog.querySelector('.stats-bar');
            
            const newEvent = document.createElement('div');
            newEvent.className = 'event-item';
            newEvent.innerHTML = `
                <div class="status-dot status-complete"></div>
                <div class="event-content">
                    <div>
                        <span class="student-name">신규학생</span>
                        <span class="class-badge">3-1</span>
                        <span class="event-description">새로운 활동이 감지되었습니다</span>
                    </div>
                </div>
                <div class="event-meta">
                    <span class="score-badge">NEW</span>
                    <span class="time-stamp">방금 전</span>
                </div>
            `;
            
            // 첫 번째 이벤트 아이템 앞에 삽입
            const firstEventItem = eventLog.querySelector('.event-item');
            eventLog.insertBefore(newEvent, firstEventItem);
            
            // 신규 이벤트 하이라이트
            newEvent.style.background = '#fef3c7';
            setTimeout(() => {
                newEvent.style.background = '';
            }, 2000);
        }

        function filterEvents(type) {
            // 탭 활성화
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // 필터링 로직 (실제로는 서버에서 필터된 데이터를 가져와야 함)
            const events = document.querySelectorAll('.event-item');
            events.forEach(event => {
                if (type === 'all') {
                    event.style.display = 'flex';
                } else {
                    const statusDot = event.querySelector('.status-dot');
                    if (statusDot.classList.contains(`status-${type}`)) {
                        event.style.display = 'flex';
                    } else {
                        event.style.display = 'none';
                    }
                }
            });
        }

        // 실시간 업데이트 시뮬레이션
        setInterval(() => {
            // 랜덤하게 새 이벤트 추가
            if (Math.random() < 0.1) { // 10% 확률
                addNewEvent();
            }
        }, 10000); // 10초마다 체크
    </script>
</body>
</html>