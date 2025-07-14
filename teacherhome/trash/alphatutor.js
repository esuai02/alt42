// 메뉴 구조 데이터
const menuStructure = {
    quarterly: {
        title: '분기활동',
        tabs: [
            {
                id: 'planning',
                title: '계획관리',
                description: '장기 목표 설정 및 관리',
                items: [
                    '분기목표 설정 도우미',
                    '분기목표 요청',
                    '장기적인 성장전망',
                    '주간목표 분석',
                    '학교생활 도우미 (기존 GPTs 연결)'
                ]
            },
            {
                id: 'counseling',
                title: '학부모상담',
                description: '학부모와의 소통 관리',
                items: [
                    '성적관리',
                    '일정관리',
                    '과제관리',
                    '도전관리',
                    '상담관리',
                    '상담앱 활용',
                    '상담지연 관리',
                    '다음 분기 시나리오 관리'
                ]
            }
        ]
    },
    weekly: {
        title: '주간활동',
        tabs: [
            {
                id: 'planning',
                title: '계획관리',
                description: '주간 목표 설정 및 관리',
                items: [
                    '주간목표 설정 도우미',
                    '주간목표 요청',
                    '분기단위 성장 전망',
                    '오늘목표 분석',
                    '주간활동 개선 리포트'
                ]
            },
            {
                id: 'completion',
                title: '완성도 관리',
                description: '학습 완성도 체크',
                items: [
                    '테스트 점수',
                    '복습',
                    '오답노트 실행'
                ]
            },
            {
                id: 'diagnosis',
                title: '종합진단',
                description: '학습 상태 종합 분석',
                items: [
                    '이탈감지',
                    '이상패턴',
                    '시험대비 상황 관리',
                    '학습모드 최적화'
                ]
            },
            {
                id: 'exam',
                title: '시험대비 진단',
                description: '시험 준비 상태 점검',
                items: [
                    '시험대비',
                    '활동최적화',
                    'Final Retrieval'
                ]
            }
        ]
    },
    daily: {
        title: '오늘활동',
        tabs: [
            {
                id: 'planning',
                title: '계획관리',
                description: '일일 목표 설정 및 관리',
                items: [
                    '오늘목표 설정 도우미',
                    '포모도르 요청',
                    '주단위 성장 전망',
                    '오늘활동 개선 리포트',
                    '지각관리, 보강관리',
                    '데스크 소통'
                ]
            },
            {
                id: 'dopamine',
                title: '실시간_도파민',
                description: '학습 동기 관리',
                items: [
                    '토닉 도파민',
                    '페이직 도파민'
                ]
            }
        ]
    },
    realtime: {
        title: '실시간 관리',
        tabs: [
            {
                id: 'management',
                title: '실시간 관리',
                description: '현재 상태 모니터링',
                items: [
                    '침착도',
                    '점수관리',
                    '오답노트',
                    '휴식관리',
                    '포모도르 학습일지 분석'
                ]
            }
        ]
    },
    interaction: {
        title: '상호작용 관리',
        tabs: [
            {
                id: 'management',
                title: '상호작용 관리',
                description: '소통 최적화',
                items: [
                    '사용법 고도화',
                    '개선지점 포착',
                    '하이튜터링',
                    'tts 활용',
                    '질의응답'
                ]
            }
        ]
    },
    bias: {
        title: '편향관리',
        tabs: [
            {
                id: 'management',
                title: '편향관리',
                description: '인지 편향 관리',
                items: [
                    '학습인지 편향',
                    '메타인지 편향'
                ]
            }
        ]
    }
};

// 전역 변수
let currentCategory = null;
let currentTab = null;
let currentViewMode = 'onboarding';
let messages = [];

// 초기화
function initializeApp() {
    setupEventListeners();
    
    // 기본 헤더 정보 설정
    document.getElementById('currentAgentName').textContent = '교육 AI 시스템';
    document.getElementById('currentAgentRole').textContent = '좌측 메뉴에서 원하는 기능을 선택해주세요';
    document.getElementById('currentAgentAvatar').style.background = 'linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%)';
    document.getElementById('currentAgentAvatar').textContent = '🎓';
    
    // 가이드 메시지 업데이트
    const guideMessage = document.getElementById('guideMessage');
    guideMessage.textContent = '💡 좌측 메뉴에서 원하는 기능을 선택하여 시작해보세요!';
    
    // 기본 환영 메시지 표시
    showWelcomeMessage();
}

// 환영 메시지 표시
function showWelcomeMessage() {
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; color: #9ca3af;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎓</div>
            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">교육 AI 시스템에 오신 것을 환영합니다!</h3>
            <p style="margin-bottom: 2rem; max-width: 500px; line-height: 1.6;">
                좌측 메뉴에서 원하는 기능을 선택하여 시작해보세요.<br>
                분기활동, 주간활동, 오늘활동 등 다양한 교육 관리 도구가 준비되어 있습니다.
            </p>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; max-width: 400px;">
                <div style="background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); padding: 1rem; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📅</div>
                    <div style="font-size: 0.875rem; font-weight: 600;">분기활동</div>
                    <div style="font-size: 0.75rem; opacity: 0.8;">장기 계획 관리</div>
                </div>
                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1rem; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📊</div>
                    <div style="font-size: 0.875rem; font-weight: 600;">실시간 관리</div>
                    <div style="font-size: 0.75rem; opacity: 0.8;">현재 상태 모니터링</div>
                </div>
                <div style="background: linear-gradient(135deg, #f97316 0%, #dc2626 100%); padding: 1rem; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">💬</div>
                    <div style="font-size: 0.875rem; font-weight: 600;">상호작용 관리</div>
                    <div style="font-size: 0.75rem; opacity: 0.8;">소통 최적화</div>
                </div>
                <div style="background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); padding: 1rem; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🧠</div>
                    <div style="font-size: 0.875rem; font-weight: 600;">편향관리</div>
                    <div style="font-size: 0.75rem; opacity: 0.8;">인지 최적화</div>
                </div>
            </div>
        </div>
    `;
}

// 이벤트 리스너 설정
function setupEventListeners() {
    const messageInput = document.getElementById('messageInput');
    const searchInput = document.getElementById('searchInput');

    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    searchInput.addEventListener('input', (e) => {
        // 검색 기능은 필요시 구현
    });
}

// 카테고리 선택
function selectCategory(categoryId) {
    currentCategory = categoryId;
    currentTab = null;
    
    // 좌측 메뉴 하이라이트 업데이트
    updateCategoryHighlight(categoryId);
    
    // 메뉴탭 표시
    showMenuTabs(categoryId);
    
    // 헤더 정보 업데이트
    updateHeaderInfo(categoryId);
    
    // 온보딩 모드인 경우 스토리 시작
    if (currentViewMode === 'onboarding') {
        startCategoryOnboarding(categoryId);
    }
}

// 좌측 메뉴 하이라이트 업데이트
function updateCategoryHighlight(categoryId) {
    // 모든 카테고리 헤더 비활성화
    document.querySelectorAll('.category-header').forEach(header => {
        header.classList.remove('active');
    });
    
    // 모든 상태 표시 비활성화
    document.querySelectorAll('.category-status').forEach(status => {
        status.classList.remove('active');
    });
    
    // 선택된 카테고리 활성화
    const categoryElement = document.querySelector(`[data-category="${categoryId}"] .category-header`);
    const statusElement = document.getElementById(`${categoryId}-status`);
    
    if (categoryElement) categoryElement.classList.add('active');
    if (statusElement) statusElement.classList.add('active');
}

// 메뉴탭 표시
function showMenuTabs(categoryId) {
    const menuTabContainer = document.getElementById('menuTabContainer');
    const menuTabGrid = document.getElementById('menuTabGrid');
    const submenuContainer = document.getElementById('submenuContainer');
    
    const categoryData = menuStructure[categoryId];
    if (!categoryData) return;
    
    // 메뉴탭 컨테이너 표시
    menuTabContainer.classList.add('active');
    
    // 메뉴탭 그리드 생성
    menuTabGrid.innerHTML = '';
    categoryData.tabs.forEach(tab => {
        const tabElement = document.createElement('div');
        tabElement.className = 'menu-tab-item';
        tabElement.onclick = () => selectTab(categoryId, tab.id);
        
        tabElement.innerHTML = `
            <div class="menu-tab-title">${tab.title}</div>
            <div class="menu-tab-description">${tab.description}</div>
        `;
        
        menuTabGrid.appendChild(tabElement);
    });
    
    // 첫 번째 탭 자동 선택
    if (categoryData.tabs.length > 0) {
        selectTab(categoryId, categoryData.tabs[0].id);
    }
    
    // 서브메뉴 컨테이너 숨김
    submenuContainer.classList.remove('active');
}

// 탭 선택
function selectTab(categoryId, tabId) {
    currentTab = tabId;
    
    // 탭 하이라이트 업데이트
    document.querySelectorAll('.menu-tab-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const selectedTab = event ? event.currentTarget : document.querySelector('.menu-tab-item');
    if (selectedTab) selectedTab.classList.add('active');
    
    // 서브메뉴 표시
    showSubmenu(categoryId, tabId);
}

// 서브메뉴 표시
function showSubmenu(categoryId, tabId) {
    const submenuContainer = document.getElementById('submenuContainer');
    const categoryData = menuStructure[categoryId];
    const tabData = categoryData.tabs.find(tab => tab.id === tabId);
    
    if (!tabData) return;
    
    submenuContainer.innerHTML = '';
    submenuContainer.classList.add('active');
    
    tabData.items.forEach(item => {
        const itemElement = document.createElement('button');
        itemElement.className = 'submenu-item';
        itemElement.textContent = item;
        itemElement.onclick = () => selectSubmenuItem(item);
        
        submenuContainer.appendChild(itemElement);
    });
}

// 서브메뉴 아이템 선택
function selectSubmenuItem(itemName) {
    // 서브메뉴 아이템 하이라이트 업데이트
    document.querySelectorAll('.submenu-item').forEach(item => {
        item.classList.remove('active');
    });
    
    event.currentTarget.classList.add('active');
    
    // 채팅 모드로 전환
    switchMode('chat');
    
    // 메뉴 실행 메시지 추가
    addMessage(`"${itemName}" 기능을 실행합니다.`, 'agent');
    
    // 상세 응답 생성
    setTimeout(() => {
        generateDetailResponse(itemName);
    }, 1000);
}

// 헤더 정보 업데이트
function updateHeaderInfo(categoryId) {
    const categoryData = menuStructure[categoryId];
    if (!categoryData) return;
    
    document.getElementById('currentAgentName').textContent = categoryData.title;
    document.getElementById('currentAgentRole').textContent = `${categoryData.title} 관리 시스템`;
    
    // 가이드 메시지 업데이트
    const guideMessage = document.getElementById('guideMessage');
    guideMessage.textContent = `💡 ${categoryData.title} 관련 기능을 선택해주세요!`;
}

// 모드 전환
function switchMode(mode) {
    currentViewMode = mode;
    
    // 모드 버튼 업데이트
    document.querySelectorAll('.mode-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    const activeButton = document.querySelector(`[onclick="switchMode('${mode}')"]`);
    if (activeButton) activeButton.classList.add('active');
    
    // 가이드 메시지 표시/숨김
    const guideMessage = document.getElementById('guideMessage');
    const menuTabContainer = document.getElementById('menuTabContainer');
    const chatArea = document.getElementById('chatArea');
    
    if (mode === 'chat') {
        guideMessage.classList.remove('hidden');
        menuTabContainer.classList.remove('active');
        chatArea.style.display = 'block';
    } else if (mode === 'menu') {
        guideMessage.classList.add('hidden');
        if (currentCategory) {
            menuTabContainer.classList.add('active');
        }
        chatArea.style.display = 'block';
    } else if (mode === 'onboarding') {
        guideMessage.classList.add('hidden');
        menuTabContainer.classList.remove('active');
        chatArea.style.display = 'block';
        if (currentCategory) {
            startCategoryOnboarding(currentCategory);
        } else {
            showWelcomeMessage();
        }
    }
}

// 카테고리 온보딩 시작
function startCategoryOnboarding(categoryId) {
    const categoryData = menuStructure[categoryId];
    if (!categoryData) return;
    
    // 채팅 영역 초기화
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.innerHTML = '';
    
    // 온보딩 메시지 시작
    setTimeout(() => {
        addMessage(`안녕하세요! ${categoryData.title} 관리 시스템입니다.`, 'agent');
        
        setTimeout(() => {
            addMessage(`${categoryData.title}에서는 다음과 같은 기능들을 사용할 수 있습니다:`, 'agent');
            
            setTimeout(() => {
                let tabsList = categoryData.tabs.map(tab => `• ${tab.title}: ${tab.description}`).join('\n');
                addMessage(tabsList, 'agent');
                
                setTimeout(() => {
                    addMessage('어떤 기능을 사용해보시겠습니까? 상단의 메뉴 버튼을 클릭해주세요!', 'agent');
                    
                    // 메뉴탭 표시
                    showMenuTabs(categoryId);
                }, 2000);
            }, 1500);
        }, 1500);
    }, 1000);
}

// 메시지 추가
function addMessage(text, sender) {
    const time = new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });
    const messageId = Date.now();
    
    const message = {
        id: messageId,
        text: text,
        sender: sender,
        time: time
    };
    
    messages.push(message);
    
    // DOM에 메시지 추가
    addMessageElement(text, sender, time);
    
    // 스크롤을 맨 아래로
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// 메시지 DOM 요소 추가
function addMessageElement(text, sender, time) {
    const chatContainer = document.getElementById('chatContainer');
    const messageElement = document.createElement('div');
    messageElement.className = `message ${sender}`;
    
    const messageContent = document.createElement('div');
    messageContent.className = 'message-content';
    messageContent.innerHTML = `<p style="white-space: pre-line;">${text}</p>`;
    
    const messageTime = document.createElement('div');
    messageTime.className = 'message-time';
    messageTime.innerHTML = `
        <span>${time}</span>
        ${sender === 'teacher' ? '<span>✓</span>' : ''}
    `;
    
    messageElement.appendChild(messageContent);
    messageElement.appendChild(messageTime);
    chatContainer.appendChild(messageElement);
}

// 메시지 전송
function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // 사용자 메시지 추가
    addMessage(message, 'teacher');
    
    // 입력 필드 초기화
    input.value = '';
    
    // AI 응답 생성
    setTimeout(() => {
        generateAIResponse(message);
    }, 1000);
}

// AI 응답 생성
function generateAIResponse(userQuery) {
    let responseText = '';
    
    if (currentCategory && currentTab) {
        const categoryData = menuStructure[currentCategory];
        responseText = `${categoryData.title}에 대한 질문을 해주셨네요. 더 구체적인 도움을 드릴 수 있도록 상세한 정보를 알려주세요.`;
    } else {
        responseText = '질문해주셔서 감사합니다. 좌측 메뉴에서 관련 기능을 선택하시면 더 정확한 답변을 드릴 수 있습니다.';
    }
    
    addMessage(responseText, 'agent');
}

// 상세 응답 생성
function generateDetailResponse(menuName) {
    let detailResponse = '';
    
    switch(menuName) {
        // 분기활동 - 계획관리
        case '분기목표 설정 도우미':
            detailResponse = '📊 분기목표 설정을 도와드리겠습니다.\n• 현재 분기 진행률: 45%\n• 목표 달성률: 78%\n• 다음 분기 계획 수립 중...';
            break;
        case '분기목표 요청':
            detailResponse = '🎯 분기목표 요청 처리\n• 목표 설정 완료\n• 개인별 맞춤 목표 생성\n• 진도 계획 수립 중...';
            break;
        case '장기적인 성장전망':
            detailResponse = '📈 장기 성장 전망 분석\n• 학습 성장률: +15%\n• 예상 성취도: 85%\n• 개선 영역: 3개 분야';
            break;
        case '주간목표 분석':
            detailResponse = '📅 주간목표 분석 결과\n• 목표 달성률: 82%\n• 우수 항목: 5개\n• 개선 필요: 2개 항목';
            break;
        case '학교생활 도우미':
            detailResponse = '🏫 학교생활 도우미 연결\n• 기존 GPTs 시스템 연결\n• 종합 생활 관리 시작\n• 실시간 지원 가능';
            break;
        
        // 분기활동 - 학부모상담
        case '성적관리':
            detailResponse = '📊 성적관리 현황\n• 학급 평균: 78.5점\n• 상위 20%: 92점 이상\n• 개선 필요: 5명 (개별 지도 계획 수립)';
            break;
        case '일정관리':
            detailResponse = '📅 일정관리 시스템\n• 개인별 스케줄 최적화\n• 시험 일정 관리\n• 과외 활동 조정';
            break;
        case '과제관리':
            detailResponse = '📝 과제관리 현황\n• 제출률: 94%\n• 평균 점수: 85점\n• 미제출자: 2명 (개별 지도 중)';
            break;
        case '도전관리':
            detailResponse = '🎯 도전관리 시스템\n• 도전 과제 진행률: 76%\n• 성공률: 88%\n• 새로운 도전 과제 3개 추천';
            break;
        case '상담관리':
            detailResponse = '💬 상담관리 현황\n• 이번 주 상담 예정: 3건\n• 완료된 상담: 8건\n• 긴급 상담 필요: 1건';
            break;
        case '상담앱 활용':
            detailResponse = '📱 상담앱 활용 가이드\n• 실시간 채팅 상담\n• 영상 상담 예약\n• 상담 기록 관리';
            break;
        case '상담지연 관리':
            detailResponse = '⏰ 상담지연 관리\n• 지연 사유 분석\n• 대체 방안 제시\n• 우선순위 재조정';
            break;
        case '다음 분기 시나리오 관리':
            detailResponse = '➡️ 다음 분기 시나리오\n• 성장 예측 모델링\n• 맞춤형 계획 수립\n• 리스크 요소 분석';
            break;
        
        // 주간활동 - 계획관리
        case '주간목표 설정 도우미':
            detailResponse = '📅 주간목표 설정\n• 개인별 목표 3개 설정\n• 우선순위 결정\n• 실행 계획 수립';
            break;
        case '주간목표 요청':
            detailResponse = '🎯 주간목표 요청 처리\n• 목표 승인 완료\n• 세부 계획 생성\n• 진도 체크 시작';
            break;
        case '분기단위 성장 전망':
            detailResponse = '📈 분기 성장 전망\n• 현재 성장률: 12%\n• 예상 달성률: 89%\n• 보완 계획 수립';
            break;
        case '오늘목표 분석':
            detailResponse = '✅ 오늘목표 분석\n• 목표 달성률: 85%\n• 완료 항목: 7개\n• 미완료 항목: 2개';
            break;
        case '주간활동 개선 리포트':
            detailResponse = '📊 주간활동 개선 리포트\n• 개선율: 18%\n• 우수 영역: 4개\n• 개선 방안: 3개 제시';
            break;
        
        // 주간활동 - 완성도 관리
        case '테스트 점수':
            detailResponse = '🎯 테스트 점수 관리\n• 평균 점수: 84점\n• 최고 점수: 98점\n• 개선 필요: 3명';
            break;
        case '복습':
            detailResponse = '📚 복습 관리 시스템\n• 복습 완료율: 76%\n• 이해도 향상: 15%\n• 추가 복습 필요: 4개 단원';
            break;
        case '오답노트 실행':
            detailResponse = '❌ 오답노트 실행\n• 오답 문제 분석\n• 유형별 분류 완료\n• 개선 방안 제시';
            break;
        
        // 주간활동 - 종합진단
        case '이탈감지':
            detailResponse = '⚠️ 이탈감지 시스템\n• 위험군 학생: 2명\n• 조기 경고 발령\n• 개입 계획 수립';
            break;
        case '이상패턴':
            detailResponse = '⚡ 이상패턴 감지\n• 비정상적 학습 패턴 3건\n• 원인 분석 완료\n• 개선 방안 제시';
            break;
        case '시험대비 상황 관리':
            detailResponse = '📋 시험대비 상황\n• 준비도: 78%\n• 취약 과목: 2개\n• 집중 관리 필요: 5명';
            break;
        case '학습모드 최적화':
            detailResponse = '🎯 학습모드 최적화\n• 개인별 최적 모드 설정\n• 효율성 15% 향상\n• 맞춤형 학습 계획 적용';
            break;
        
        // 주간활동 - 시험대비 진단
        case '시험대비':
            detailResponse = '📝 시험대비 진단\n• 준비 완료율: 82%\n• 예상 성적: 85점\n• 마지막 점검 항목: 3개';
            break;
        case '활동최적화':
            detailResponse = '⚡ 활동최적화\n• 학습 효율성 18% 향상\n• 시간 배분 최적화\n• 집중도 관리 시스템 적용';
            break;
        case 'Final Retrieval':
            detailResponse = '🎯 Final Retrieval\n• 최종 점검 완료\n• 핵심 내용 정리\n• 시험 전략 수립';
            break;
        
        // 오늘활동 - 계획관리
        case '오늘목표 설정 도우미':
            detailResponse = '✅ 오늘목표 설정\n• 개인별 목표 3개 설정\n• 우선순위 결정\n• 시간 배분 계획';
            break;
        case '포모도르 요청':
            detailResponse = '⏰ 포모도르 기법 적용\n• 집중 시간: 25분 → 35분 증가\n• 휴식 효율성: 87% 향상\n• 권장 사이클: 45분 집중 + 10분 휴식';
            break;
        case '주단위 성장 전망':
            detailResponse = '📈 주단위 성장 전망\n• 성장률: 8%\n• 예상 달성률: 92%\n• 개선 영역: 2개';
            break;
        case '오늘활동 개선 리포트':
            detailResponse = '📊 오늘활동 개선 리포트\n• 개선율: 12%\n• 효율성 증가: 15%\n• 추천 활동: 3개';
            break;
        case '지각관리, 보강관리':
            detailResponse = '⏰ 지각 및 보강 관리\n• 지각 횟수: 감소 중\n• 보강 계획 수립\n• 개선 방안 적용';
            break;
        case '데스크 소통':
            detailResponse = '💬 데스크 소통 시스템\n• 실시간 질문 답변\n• 학습 지원 제공\n• 개별 맞춤 지도';
            break;
        
        // 오늘활동 - 실시간 도파민
        case '토닉 도파민':
            detailResponse = '⚡ 토닉 도파민 관리\n• 기본 동기 수준: 85%\n• 지속적 동기 유지\n• 안정적 학습 환경 조성';
            break;
        case '페이직 도파민':
            detailResponse = '⚡ 페이직 도파민 관리\n• 순간적 보상 시스템\n• 성취감 증진\n• 학습 동기 극대화';
            break;
        
        // 실시간 관리
        case '침착도':
            detailResponse = '👁️ 침착도 측정\n• 현재 침착도: 92%\n• 스트레스 수준: 낮음\n• 최적 학습 상태 유지';
            break;
        case '점수관리':
            detailResponse = '⭐ 점수관리 시스템\n• 실시간 점수: 94점\n• 학급 순위: 3위\n• 개선 포인트: 2개';
            break;
        case '오답노트':
            detailResponse = '❌ 오답노트 관리\n• 오답 문제 15개\n• 유형별 분석 완료\n• 복습 계획 수립';
            break;
        case '휴식관리':
            detailResponse = '☕ 휴식관리 시스템\n• 적절한 휴식 간격\n• 회복 시간 최적화\n• 지속적 집중력 유지';
            break;
        case '포모도르 학습일지 분석':
            detailResponse = '📊 포모도르 학습일지\n• 집중 시간 분석\n• 효율성 측정\n• 개선 방안 제시';
            break;
        
        // 상호작용 관리
        case '사용법 고도화':
            detailResponse = '📈 사용법 고도화\n• 시스템 활용도 증가\n• 효율성 개선\n• 맞춤형 기능 제공';
            break;
        case '개선지점 포착':
            detailResponse = '💡 개선지점 포착\n• 개선 포인트 5개 발견\n• 우선순위 설정\n• 실행 계획 수립';
            break;
        case '하이튜터링':
            detailResponse = '🎯 하이튜터링 시스템\n• 개인별 맞춤 지도\n• 실시간 피드백\n• 학습 효과 극대화';
            break;
        case 'tts 활용':
            detailResponse = '🔊 TTS 활용 시스템\n• 음성 학습 지원\n• 청각 학습 최적화\n• 다양한 학습 스타일 지원';
            break;
        case '질의응답':
            detailResponse = '❓ 질의응답 시스템\n• 실시간 질문 처리\n• 즉시 답변 제공\n• 학습 지원 강화';
            break;
        
        // 편향관리
        case '학습인지 편향':
            detailResponse = '🧠 학습인지 편향 분석\n• 편향 패턴 3개 감지\n• 개선 방안 제시\n• 객관적 학습 유도';
            break;
        case '메타인지 편향':
            detailResponse = '🎯 메타인지 편향 관리\n• 자기 인식 개선\n• 학습 전략 최적화\n• 효과적 자기 조절';
            break;
        
        default:
            detailResponse = `${menuName} 기능이 실행되었습니다. 상세 분석을 진행하겠습니다.`;
    }
    
    addMessage(detailResponse, 'agent');
}

// 앱 초기화
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});