/**
 * 교육 AI 시스템 - 메인 JavaScript 파일
 * 작성일: 2024-07-09
 * 설명: 온보딩/메뉴/채팅 탭을 분리한 교육 AI 시스템
 */

// ==================== 모듈 통합 관리 ====================
let currentCategory = null;
let currentMode = 'onboarding';
let currentTab = null;
let currentItem = null;
let currentStep = 'welcome';
let actionInProgress = false;
let progressInterval = null;

// 11가지 플러그인 유형 정의
const pluginTypes = [
    { id: 'internal_link', title: '내부링크 열기', icon: '🔗', description: '플랫폼 내 다른 페이지로 이동' },
    { id: 'external_link', title: '외부링크 열기', icon: '🌐', description: '외부 사이트나 도구 연결' },
    { id: 'custom_interface', title: '맞춤 인터페이스', icon: '🎨', description: '폼, 차트, 위젯 등 맞춤형 UI 생성' },
    { id: 'send_message', title: '메시지 발송', icon: '📨', description: '사용자에게 자동 메시지 전송' },
    { id: 'bulk_message', title: '유사 메시지 함께 발송', icon: '📩', description: '관련 메시지 자동 추가 전송' },
    { id: 'reminder', title: '학습 리마인더 생성기', icon: '⏰', description: '과제/복습 일정 자동 리마인드' },
    { id: 'progress_visual', title: '진도 대비도 시각화', icon: '📊', description: '목표 대비 진도 그래프 제공' },
    { id: 'feedback_card', title: '피드백 수집 카드', icon: '📝', description: '설문이나 의견 수집용 간단 카드 생성' },
    { id: 'mood_checkin', title: '학습 기분 체크인', icon: '😊', description: '감정 상태 기반 튜터링 UX 변화' },
    { id: 'interaction_history', title: '상호작용 히스토리 카드', icon: '📜', description: '학습 중 요청, 피드백 기록 시각화' },
    { id: 'strategy_recommender', title: '학습 전략 추천기', icon: '🎯', description: '공부 성향 기반 맞춤 전략 추천' }
];

// 기존 메뉴 항목들 (맞춤 인터페이스로 이동)
const existingMenuItems = [];

// 사용자 선택 플러그인 카드 저장
let userSelectedPlugins = [];

// 모듈별 데이터를 통합하여 메뉴 구조 생성
function getMenuStructure() {
    return {
        quarterly: window.quarterlyModule ? window.quarterlyModule.getData() : null,
        weekly: window.weeklyModule ? window.weeklyModule.getData() : null,
        daily: window.dailyModule ? window.dailyModule.getData() : null,
        realtime: window.realtimeModule ? window.realtimeModule.getData() : null,
        interaction: window.interactionModule ? window.interactionModule.getData() : null,
        bias: window.biasModule ? window.biasModule.getData() : null,
        development: window.developmentModule ? window.developmentModule.getData() : null,
        branding: getBrandingData()
    };
}

// ==================== 상태 관리 ====================
const agents = {
    quarterly: { name: '분기 관리자', role: '장기 계획 및 목표 관리', avatar: '📅', status: 'online' },
    weekly: { name: '주간 관리자', role: '주간 활동 및 진도 관리', avatar: '📝', status: 'online' },
    daily: { name: '일일 관리자', role: '오늘의 활동 및 목표 관리', avatar: '⏰', status: 'online' },
    realtime: { name: '실시간 관리자', role: '즉시 모니터링 및 대응', avatar: '📊', status: 'online' },
    interaction: { name: '상호작용 관리자', role: '소통 및 피드백 관리', avatar: '💬', status: 'online' },
    bias: { name: '인지관성 개선 관리자', role: '수학 학습 인지관성 개선 및 연쇄상호작용 관리', avatar: '🧠', status: 'online' },
    development: { name: '개발 관리자', role: '컨텐츠 및 앱 개발', avatar: '🛠️', status: 'online' },
    branding: { name: '퍼스널 브랜딩 매니저', role: '개인 브랜드 구축 및 콘텐츠 전략 관리', avatar: '🌟', status: 'online' }
};

// ==================== UI 업데이트 함수 ====================
function updateCurrentAgent(category) {
    const agent = agents[category];
    if (!agent) return;

    document.getElementById('currentAgentAvatar').textContent = agent.avatar;
    document.getElementById('currentAgentName').textContent = agent.name;
    document.getElementById('currentAgentRole').textContent = agent.role;
}

function updateCategoryStatus(category, status) {
    const statusElement = document.getElementById(`${category}-status`);
    if (statusElement) {
        statusElement.textContent = status === 'active' ? '🟢' : '●';
    }
}

// ==================== 카테고리 선택 ====================
function selectCategory(category) {
    // 초기 iframe 숨기기
    const initialIframe = document.getElementById('initialIframeContainer');
    if (initialIframe) {
        initialIframe.style.display = 'none';
    }
    
    // UI 요소들 표시
    document.querySelector('.content-header').style.display = 'flex';
    document.querySelector('.input-area').style.display = 'flex';
    document.getElementById('guideMessage').style.display = 'block';
    
    // 이전 선택 해제
    if (currentCategory) {
        updateCategoryStatus(currentCategory, 'inactive');
        const prevElement = document.querySelector(`[data-category="${currentCategory}"]`);
        if (prevElement) prevElement.classList.remove('active');
    }

    // 새 카테고리 선택
    currentCategory = category;
    updateCategoryStatus(category, 'active');
    const categoryElement = document.querySelector(`[data-category="${category}"]`);
    if (categoryElement) categoryElement.classList.add('active');

    // 에이전트 정보 업데이트
    updateCurrentAgent(category);

    // 기본적으로 온보딩 모드로 시작 (기존 동작 복원)
    currentMode = 'onboarding';
    
    // 모드 버튼 활성화 상태 업데이트
    document.querySelectorAll('.mode-button').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[onclick="switchMode('onboarding')"]`).classList.add('active');
    
    // 온보딩 시작
    document.getElementById('chatArea').style.display = 'block';
    document.getElementById('menuTabContainer').style.display = 'none';
    
    startCategoryOnboarding(category);

    // 모듈별 초기화 함수 호출
    initializeModule(category);
}

// ==================== 모듈 초기화 ====================
function initializeModule(category) {
    switch(category) {
        case 'quarterly':
            if (window.quarterlyModule) {
                console.log('분기활동 모듈 초기화');
            }
            break;
        case 'weekly':
            if (window.weeklyModule) {
                console.log('주간활동 모듈 초기화');
            }
            break;
        case 'daily':
            if (window.dailyModule) {
                console.log('오늘활동 모듈 초기화');
            }
            break;
        case 'realtime':
            if (window.realtimeModule) {
                window.realtimeModule.startMonitoring();
            }
            break;
        case 'interaction':
            if (window.interactionModule) {
                window.interactionModule.startConversation();
            }
            break;
        case 'bias':
            if (window.biasModule) {
                window.biasModule.detectBias();
            }
            break;
        case 'development':
            if (window.developmentModule) {
                console.log('개발 모듈 초기화');
            }
            break;
        case 'branding':
            console.log('퍼스널 브랜딩 모듈 초기화');
            break;
    }
}

// ==================== 모드 전환 ====================
function switchMode(mode) {
    currentMode = mode;
    
    // 초기 iframe 숨기기 (카테고리가 선택된 경우에만)
    if (currentCategory) {
        const initialIframe = document.getElementById('initialIframeContainer');
        if (initialIframe) {
            initialIframe.style.display = 'none';
        }
    }
    
    // 모든 모드 버튼 비활성화
    document.querySelectorAll('.mode-button').forEach(btn => btn.classList.remove('active'));
    
    // 선택된 모드 버튼 활성화
    const modeButton = document.querySelector(`[onclick="switchMode('${mode}')"]`);
    if (modeButton) modeButton.classList.add('active');
    
    // UI 요소 표시/숨김
    const menuTabContainer = document.getElementById('menuTabContainer');
    const chatArea = document.getElementById('chatArea');
    const guideMessage = document.getElementById('guideMessage');
    
    menuTabContainer.style.display = 'none';
    chatArea.style.display = 'none';
    guideMessage.style.display = 'none';
    
    if (mode === 'onboarding') {
        chatArea.style.display = 'block';
        guideMessage.style.display = 'block';
        
        if (currentCategory) {
            startCategoryOnboarding(currentCategory);
        } else {
            showWelcomeMessage();
        }
    } else if (mode === 'menu') {
        menuTabContainer.style.display = 'block';
        
        if (currentCategory) {
            showMenuInterface(currentCategory);
        } else {
            showMenuWelcome();
        }
    } else if (mode === 'chat') {
        chatArea.style.display = 'block';
        guideMessage.style.display = 'block';
        showChatInterface();
    }
}

// ==================== 온보딩 모드 ====================
function startCategoryOnboarding(category) {
    const menuStructure = getMenuStructure();
    const categoryData = menuStructure[category];
    
    if (!categoryData) {
        console.error(`카테고리 데이터를 찾을 수 없습니다: ${category}`);
        return;
    }

    // 채팅 초기화
    clearChat();
    
    // 인지관성 개선 카테고리에 특화된 온보딩
    if (category === 'bias') {
        startMathCognitionOnboarding(categoryData);
    } else {
        // 기존 온보딩 방식
        startDefaultOnboarding(category, categoryData);
    }
}

// 수학 인지관성 개선 전용 온보딩
function startMathCognitionOnboarding(categoryData) {
    // 인사 메시지
    setTimeout(() => {
        addMessage('ai', `안녕하세요! 🧠 수학 학습 인지관성 개선 전문가입니다.`);
    }, 500);

    setTimeout(() => {
        addMessage('ai', `수학 공부하면서 이런 고민 해보셨나요? 🤔`);
    }, 1500);

    setTimeout(() => {
        addMessage('ai', `"왜 같은 유형 문제인데 자꾸 틀리지?" "개념은 아는데 문제만 보면 막막해..." "시간이 부족해서 마지막 문제까지 못 풀었어..."`);
    }, 2500);

    setTimeout(() => {
        addMessage('ai', `이런 문제들은 사실 개별 학생의 '인지관성' 패턴과 관련이 있어요. 📊`);
    }, 4000);

    setTimeout(() => {
        addMessage('ai', `저는 각 학생의 수학 학습 패턴을 분석하고, 비슷한 어려움을 겪는 다른 학생들과 연결해서 함께 해결해나가는 '연쇄상호작용' 시스템을 운영합니다! ⛓️✨`);
    }, 5500);

    setTimeout(() => {
        addMessage('ai', `예를 들어, 한 학생이 '포모도르 기법'으로 집중력을 개선했다면, 비슷한 집중력 문제를 가진 다른 학생들에게도 자동으로 맞춤 솔루션을 제공해드려요.`);
    }, 7000);

    setTimeout(() => {
        addMessage('ai', `어떤 영역부터 시작해보시겠어요? 각 영역별로 맞춤형 솔루션과 연쇄상호작용을 체험해보실 수 있습니다! 🚀`);
        showSecondaryMenuCards(categoryData);
    }, 8500);
}

// 기본 온보딩 방식
function startDefaultOnboarding(category, categoryData) {
    // 인사 메시지
    setTimeout(() => {
        const agent = agents[category];
        addMessage('ai', `안녕하세요! ${agent.name}입니다. ${agent.role}를 담당하고 있습니다.`);
    }, 500);

    // 메뉴 설명
    setTimeout(() => {
        addMessage('ai', `${categoryData.title}에 대해 소개해드리겠습니다.`);
    }, 1500);

    setTimeout(() => {
        addMessage('ai', categoryData.description);
    }, 2500);

    // 2차 메뉴 선택 카드 표시
    setTimeout(() => {
        addMessage('ai', '어떤 기능을 자세히 살펴보시겠습니까?');
        showSecondaryMenuCards(categoryData);
    }, 3500);

    currentStep = 'secondary_menu_selection';
}

function showWelcomeMessage() {
    clearChat();
    
    setTimeout(() => {
        addMessage('ai', '안녕하세요! 교육 AI 시스템에 오신 것을 환영합니다! 🎉');
    }, 500);
    
    setTimeout(() => {
        addMessage('ai', '저는 여러분의 학습을 도와드리는 AI 어시스턴트입니다.');
    }, 1500);
    
    setTimeout(() => {
        addMessage('ai', '좌측 메뉴에서 원하는 기능을 선택하시면, 해당 분야의 전문 관리자가 자세히 안내해드릴게요!');
    }, 2500);
}

// ==================== 온보딩 카드 기능들 ====================
// 2차 메뉴 카드 표시 (탭 선택)
function showSecondaryMenuCards(categoryData) {
    const chatContainer = document.getElementById('chatContainer');
    const cardContainer = document.createElement('div');
    cardContainer.className = 'chat-selection-cards';
    
    // 탭 카드들
    categoryData.tabs.forEach(tab => {
        const card = document.createElement('div');
        card.className = 'chat-card';
        card.onclick = () => selectTabFromOnboarding(tab);
        card.innerHTML = `
            <div class="chat-card-header">
                <h4>${tab.title}</h4>
            </div>
            <div class="chat-card-body">
                <p>${tab.description}</p>
                <div class="chat-card-count">${tab.items.length}개 세부 기능</div>
            </div>
        `;
        cardContainer.appendChild(card);
    });

    chatContainer.appendChild(cardContainer);
    
    // 이전 메뉴 버튼 추가
    addBackButton('처음으로 돌아가기', () => {
        clearChat();
        showWelcomeMessage();
    });
    
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function selectTabFromOnboarding(tab) {
    currentTab = tab;
    currentStep = 'tab_selected';
    
    // 선택 카드 제거
    const cards = document.querySelectorAll('.chat-selection-cards');
    cards.forEach(card => card.remove());
    
    // 사용자 선택 메시지 추가
    addMessage('user', `${tab.title}을 선택했습니다.`);
    
    // 인지관성 개선 카테고리의 경우 수학 특화 메시지
    if (currentCategory === 'bias') {
        showMathSpecificTabIntro(tab);
    } else {
        // 기존 방식
        showDefaultTabIntro(tab);
    }
}

// 수학 특화 탭 소개
function showMathSpecificTabIntro(tab) {
    const mathMessages = {
        'concept_study': {
            intro: `좋은 선택이에요! 개념공부는 수학의 기초 체력과 같아요. 💪`,
            context: `많은 학생들이 "개념은 알겠는데 문제가 안 풀려요"라고 하는데, 실제로는 개념을 '아는 것'과 '활용할 수 있는 것' 사이에 큰 차이가 있거든요.`,
            solution: `여기서는 포모도르 기법부터 AI 음성대화까지, 개념을 정말 '내 것'으로 만드는 다양한 방법들을 제공합니다!`
        },
        'problem_solving': {
            intro: `문제풀이! 수학의 꽃이죠! 🌸 하지만 막상 문제를 보면... 어디서부터 시작해야 할지 모르겠죠?`,
            context: `"시작이 반이다"라는 말이 있듯이, 문제풀이도 시작을 어떻게 하느냐가 정말 중요해요. 그리고 과정에서의 점검, 마무리까지...`,
            solution: `체계적인 문제해결 전략과 함께, 비슷한 실수 패턴을 가진 친구들과의 연쇄학습으로 더 효과적으로 개선해나갈 수 있어요!`
        },
        'learning_management': {
            intro: `학습관리! 이거 정말 중요한데 소홀히 하기 쉬운 부분이에요. 📚`,
            context: `"공부는 열심히 하는데 성적이 안 오르네..." 하는 친구들 대부분이 학습관리에서 놓치는 부분들이 있어요.`,
            solution: `내공부방 세팅부터 수학일기 작성까지, 체계적인 관리 시스템으로 공부의 효율을 확실히 높여보세요!`
        },
        'exam_preparation': {
            intro: `시험대비! 가장 스트레스 받지만 가장 중요한 순간이죠! 😤`,
            context: `"시험 기간만 되면 뭘 어떻게 공부해야 할지 모르겠어요..." 이런 고민, 정말 많이 들어봤어요.`,
            solution: `준비상태 진단부터 구간별 최적화, 최종 기억인출 전략까지! 체계적인 시험 대비로 실력 발휘 100% 해보세요!`
        },
        'practical_training': {
            intro: `실전연습! 진짜 실력을 보여주는 순간이에요! ⚡`,
            context: `"평소엔 잘 푸는데 시험만 보면 시간이 부족해요", "실수가 너무 많아요" - 이런 고민들, 모두 실전 경험 부족 때문이에요.`,
            solution: `시간관리부터 실수 조절, 기회비용 계산까지! 실전에서 최고의 퍼포먼스를 낼 수 있도록 도와드릴게요!`
        },
        'attendance': {
            intro: `출결관리! 공부의 연속성을 지키는 중요한 열쇠에요! 🗝️`,
            context: `한 번 빠지면 따라잡기 어려운 수학... 하지만 어쩔 수 없이 빠지는 경우도 있죠.`,
            solution: `사전보강부터 전수보강까지, 학습의 연속성을 잃지 않도록 체계적으로 관리해드려요!`
        }
    };

    const message = mathMessages[tab.id] || {
        intro: `${tab.title}에 대해 자세히 설명드리겠습니다.`,
        context: tab.explanation,
        solution: `다양한 기능들을 통해 도움을 드릴게요!`
    };

    setTimeout(() => {
        addMessage('ai', message.intro);
    }, 500);
    
    setTimeout(() => {
        addMessage('ai', message.context);
    }, 2000);
    
    setTimeout(() => {
        addMessage('ai', message.solution);
    }, 3500);
    
    // 세부 기능 선택 카드 표시
    setTimeout(() => {
        addMessage('ai', '어떤 세부 기능으로 시작해보시겠어요? 🚀');
        showDetailMenuCards(tab.items);
    }, 5000);
}

// 기본 탭 소개
function showDefaultTabIntro(tab) {
    // AI 응답 - 하위 메뉴 소개
    setTimeout(() => {
        addMessage('ai', `${tab.title}에 대해 자세히 설명드리겠습니다.`);
    }, 500);
    
    setTimeout(() => {
        addMessage('ai', tab.explanation);
    }, 1500);
    
    // 세부 기능 선택 카드 표시
    setTimeout(() => {
        addMessage('ai', '어떤 세부 기능을 자세히 알아보시겠습니까?');
        showDetailMenuCards(tab.items);
    }, 2500);
}

// 세부 메뉴 카드 표시 (아이템 선택)
function showDetailMenuCards(items) {
    const chatContainer = document.getElementById('chatContainer');
    const cardContainer = document.createElement('div');
    cardContainer.className = 'chat-selection-cards';
    
    items.forEach(item => {
        const card = document.createElement('div');
        card.className = 'chat-card';
        card.onclick = () => selectItemFromOnboarding(item);
        card.innerHTML = `
            <div class="chat-card-header">
                <h4>${item.title}</h4>
            </div>
            <div class="chat-card-body">
                <p>${item.description}</p>
                <div class="chat-card-count">${item.details.length}개 세부 작업</div>
            </div>
        `;
        cardContainer.appendChild(card);
    });

    chatContainer.appendChild(cardContainer);
    
    // 이전 메뉴 버튼 추가
    addBackButton('이전 메뉴로', () => {
        clearChat();
        const menuStructure = getMenuStructure();
        const categoryData = menuStructure[currentCategory];
        
        setTimeout(() => {
            addMessage('ai', '어떤 기능을 자세히 살펴보시겠습니까?');
            showSecondaryMenuCards(categoryData);
        }, 100);
    });
    
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function selectItemFromOnboarding(item) {
    currentItem = item;
    
    // 선택 카드 제거
    const cards = document.querySelectorAll('.chat-selection-cards');
    cards.forEach(card => card.remove());
    
    // 사용자 선택 메시지 추가
    addMessage('user', `${item.title}에 대해 자세히 알려주세요.`);
    
    // 인지관성 개선 카테고리의 경우 수학 특화 메시지
    if (currentCategory === 'bias') {
        showMathSpecificItemIntro(item);
    } else {
        showDefaultItemIntro(item);
    }
}

// 수학 특화 세부 기능 소개
function showMathSpecificItemIntro(item) {
    const mathItemMessages = {
        '포모도르설정': {
            intro: `포모도르 기법! 집중력 문제로 고민인가요? 📚⏰`,
            context: `"30분만 앉아있어도 딴 생각이 나요", "핸드폰이 자꾸 신경 쓰여요" - 이런 고민 정말 많죠. 포모도르 기법은 단순히 25분 공부하고 5분 쉬는 게 아니에요!`,
            action: `개인별 최적 집중 시간을 찾고, 수학 문제 유형별로 시간을 조정하는 맞춤형 포모도르를 설정해드릴게요!`
        },
        '개념노트 사용법': {
            intro: `개념노트! 수학 실력의 비밀창고를 만들어봐요! 📔✨`,
            context: `"개념은 공부했는데 문제만 보면 기억이 안 나요" - 이건 개념을 단순 암기했기 때문이에요. 진짜 개념 정리는 따로 있거든요!`,
            action: `공식 암기가 아닌, 개념 간 연결고리를 만드는 노트 작성법을 알려드릴게요. 나만의 수학 지식 네트워크를 구축해봅시다!`
        },
        '음성대화 사용법': {
            intro: `AI와 수학 대화! 마치 개인 과외 선생님처럼요! 🗣️🤖`,
            context: `"혼자 공부하면 막히는 부분을 물어볼 사람이 없어요" - 이제 AI와 실시간으로 수학 대화를 나눠보세요!`,
            action: `단순 검색이 아닌, 진짜 대화를 통해 개념을 이해하고 문제 해결 과정을 함께 고민해보는 방법을 알려드릴게요!`
        },
        '문제풀이 시작': {
            intro: `문제를 마주한 그 첫 순간! 여기서 승부가 갈려요! 🎯`,
            context: `"문제를 읽어도 뭘 구하라는 건지 모르겠어요", "어떤 공식을 써야 할지 감이 안 와요" - 이런 경험 있죠?`,
            action: `문제 분석부터 접근 전략까지, 어떤 문제든 자신 있게 시작할 수 있는 체계적인 방법을 알려드릴게요!`
        },
        '시간관리 (그냥 ... , 빨리 풀기)': {
            intro: `시간관리! 실전에서 가장 중요한 스킬이에요! ⏱️💨`,
            context: `"시간 재고 풀면 다 맞는데, 시험에서는 시간이 부족해서..." - 이것도 기술이에요!`,
            action: `무작정 빨리 푸는 게 아니라, 전략적 시간 배분과 속도 조절 기법을 연습해봅시다!`
        }
    };

    const defaultMessage = {
        intro: `${item.title}! 좋은 선택이에요! 🌟`,
        context: `수학 공부에서 정말 중요한 부분이거든요.`,
        action: `지금부터 차근차근 알려드릴게요!`
    };

    const message = mathItemMessages[item.title] || defaultMessage;

    setTimeout(() => {
        addMessage('ai', message.intro);
    }, 500);
    
    setTimeout(() => {
        addMessage('ai', message.context);
    }, 2000);
    
    setTimeout(() => {
        addMessage('ai', message.action);
    }, 3500);
    
    setTimeout(() => {
        addMessage('ai', `자, 그럼 ${item.title} 기능을 실행해볼까요? 🚀`);
    }, 5000);
    
    startItemExecution(item, 6000);
}

// 기본 세부 기능 소개
function showDefaultItemIntro(item) {
    // AI 응답
    setTimeout(() => {
        addMessage('ai', `${item.title}에 대해 자세히 설명드리겠습니다.`);
    }, 500);
    
    setTimeout(() => {
        addMessage('ai', item.description);
    }, 1500);
    
    setTimeout(() => {
        addMessage('ai', `${item.title} 기능을 실행하겠습니다.`);
    }, 2500);
    
    startItemExecution(item, 3500);
}

// 공통 아이템 실행 함수
function startItemExecution(item, delay) {
    // 메뉴 탭과 동일한 방식으로 진행상황 표시
    setTimeout(() => {
        // 채팅 컨테이너에 진행 상황 표시 영역 추가 (고유 ID로 겹치지 않게)
        const chatContainer = document.getElementById('chatContainer');
        const progressId = `onboardingProgress_${Date.now()}`;
        const progressArea = document.createElement('div');
        progressArea.className = 'onboarding-progress-area';
        progressArea.innerHTML = `
            <div class="progress-header">
                <h3>🚀 ${item.title} 실행 중...</h3>
            </div>
            <div class="progress-messages" id="${progressId}"></div>
        `;
        chatContainer.appendChild(progressArea);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        
        const progressMessages = document.getElementById(progressId);
        
        setTimeout(() => {
            addOnboardingProgressMessage(progressMessages, `${item.title} 실행을 시작합니다...`);
        }, 500);
        
        // 세부 작업들 순차 실행
        item.details.forEach((detail, index) => {
            setTimeout(() => {
                addOnboardingProgressMessage(progressMessages, `✓ ${detail} - 완료`);
            }, 1500 + (index * 800));
        });
        
        // 완료 메시지 및 이전 메뉴 버튼
        setTimeout(() => {
            addOnboardingProgressMessage(progressMessages, `🎉 ${item.title} 실행이 완료되었습니다!`);
            
            // 연쇄상호작용이 가능한 항목인지 체크 (인지관성 개선 카테고리의 경우)
            if (currentCategory === 'bias' && item.hasChainInteraction) {
                setTimeout(() => {
                    showChainInteractionInterface(progressMessages, item.title, currentTab.title);
                }, 1000);
            } else {
                // 이전 메뉴 버튼 추가
                setTimeout(() => {
                    addBackButton('이전 메뉴로', () => {
                        // 직전 단계인 세부 메뉴 선택 카드로 이동
                        clearChat();
                        setTimeout(() => {
                            addMessage('ai', '어떤 세부 기능을 자세히 알아보시겠습니까?');
                            showDetailMenuCards(currentTab.items);
                        }, 100);
                    });
                }, 500);
            }
        }, 1500 + (item.details.length * 800) + 1000);
    }, delay);
}

// ==================== 메뉴 모드 ====================
function showMenuInterface(category) {
    const menuStructure = getMenuStructure();
    const categoryData = menuStructure[category];
    
    if (!categoryData) {
        showMenuWelcome();
        return;
    }

    const menuTabGrid = document.getElementById('menuTabGrid');
    const submenuContainer = document.getElementById('submenuContainer');
    
    // 퍼스널 브랜딩의 경우 특별한 iframe 인터페이스 표시
    if (category === 'branding') {
        showBrandingInterface();
        return;
    }
    
    // 메뉴 그리드 생성 - 탭 버튼들 표시
    menuTabGrid.innerHTML = `
        <div class="menu-interface">
            <h2>${agents[category].avatar} ${categoryData.title}</h2>
            <p class="menu-description">${categoryData.description}</p>
            <div class="menu-tabs-full">
                ${categoryData.tabs.map((tab, index) => `
                    <button class="menu-tab-button-full ${index === 0 ? 'active' : ''}" onclick="selectMenuTab('${tab.id}', '${tab.title}')">
                        ${tab.title}
                    </button>
                `).join('')}
            </div>
        </div>
    `;
    
    // 첫 번째 탭의 세부 메뉴를 자동으로 표시
    if (categoryData.tabs.length > 0) {
        const firstTab = categoryData.tabs[0];
        showSubmenuItems(firstTab);
    }
}

function showMenuWelcome() {
    const menuTabGrid = document.getElementById('menuTabGrid');
    const submenuContainer = document.getElementById('submenuContainer');
    
    menuTabGrid.innerHTML = `
        <div class="menu-welcome">
            <h2>메뉴 선택</h2>
            <p>좌측 메뉴에서 원하는 기능을 선택하세요.</p>
        </div>
    `;
    
    submenuContainer.innerHTML = '';
}

// 메뉴 탭 선택 함수
function selectMenuTab(tabId, tabTitle) {
    const menuStructure = getMenuStructure();
    const categoryData = menuStructure[currentCategory];
    
    if (!categoryData) return;
    
    // 선택된 탭 찾기
    const selectedTab = categoryData.tabs.find(tab => tab.id === tabId);
    if (!selectedTab) return;
    
    // 탭 버튼 활성화 상태 업데이트
    document.querySelectorAll('.menu-tab-button-full').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // 서브메뉴 표시
    showSubmenuItems(selectedTab);
}

// 서브메뉴 아이템 표시 함수
function showSubmenuItems(tab) {
    const submenuContainer = document.getElementById('submenuContainer');
    submenuContainer.innerHTML = `
        <div class="menu-tab-section">
            <h3>${tab.title}</h3>
            <p class="tab-description">${tab.description}</p>
            
            <div class="menu-cards-container">
                <div class="menu-cards-grid" id="menuCardsGrid">
                    <!-- 기존 메뉴 카드들 -->
                    ${tab.items.map((item, index) => `
                        <div class="menu-card" onclick="executeMenuAction('${item.title}', '${tab.title}')">
                            <button class="card-settings-btn" onclick="event.stopPropagation(); openContextSettings('${item.title}', '${tab.title}')">⚙️</button>
                            <div class="card-icon">📋</div>
                            <h4>${item.title}</h4>
                            <p class="card-description">${item.description}</p>
                        </div>
                    `).join('')}
                    
                    <!-- 플러그인 카드들 -->
                    ${userSelectedPlugins.map((plugin, index) => `
                        <div class="menu-card plugin-card" data-index="${index}" onclick="openPluginSettings('${plugin.id}')">
                            <button class="card-delete-btn" onclick="event.stopPropagation(); deletePlugin(${index})">×</button>
                            <div class="card-icon">${plugin.icon}</div>
                            <h4>${plugin.title}</h4>
                            <p class="card-description">${plugin.description}</p>
                        </div>
                    `).join('')}
                    
                    <!-- 플러그인 추가 카드 -->
                    <div class="menu-card add-card" onclick="showAddPluginMenu()">
                        <div class="add-icon">+</div>
                        <p>플러그인 추가</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function executeMenuAction(itemTitle, tabTitle) {
    // 현재 상태 저장
    saveCurrentState();
    
    // 인지관성 개선의 문제풀이 관련 항목들은 전용 페이지로 이동
    if (currentCategory === 'bias' && tabTitle === '문제풀이') {
        let phase = '';
        if (itemTitle === '문제풀이 시작') {
            phase = 'initial';
        } else if (itemTitle === '문제풀이 과정') {
            phase = 'middle';
        } else if (itemTitle === '문제풀이 마무리') {
            phase = 'final';
        }
        
        if (phase) {
            // 현재 창에서 bias 전용 페이지 열기
            window.location.href = `./bias/bias_interface.html?phase=${phase}&returnState=${encodeURIComponent(JSON.stringify(getCurrentState()))}`;
            return;
        }
    }
    
    // 기존 방식으로 처리
    const submenuContainer = document.getElementById('submenuContainer');
    
    // 진행 상황 표시 영역 생성 (고유 ID로 겹치지 않게)
    const progressId = `menuProgress_${Date.now()}`;
    const progressArea = document.createElement('div');
    progressArea.className = 'menu-progress-area';
    progressArea.innerHTML = `
        <h3>🚀 ${itemTitle} 실행 중...</h3>
        <div class="progress-messages" id="${progressId}"></div>
    `;
    
    submenuContainer.appendChild(progressArea);
    
    // 진행 상황 메시지들
    const progressMessages = document.getElementById(progressId);
    
    setTimeout(() => {
        addProgressMessage(progressMessages, `${itemTitle} 실행을 시작합니다...`);
    }, 500);
    
    // 현재 카테고리와 아이템 정보 찾기
    const menuStructure = getMenuStructure();
    const categoryData = menuStructure[currentCategory];
    let selectedItem = null;
    
    categoryData.tabs.forEach(tab => {
        if (tab.title === tabTitle) {
            tab.items.forEach(item => {
                if (item.title === itemTitle) {
                    selectedItem = item;
                }
            });
        }
    });
    
    if (selectedItem) {
        // 세부 작업들 순차 실행
        selectedItem.details.forEach((detail, index) => {
            setTimeout(() => {
                addProgressMessage(progressMessages, `✓ ${detail} - 완료`);
            }, 1500 + (index * 800));
        });
        
        // 완료 메시지 및 연쇄상호작용 체크
        setTimeout(() => {
            addProgressMessage(progressMessages, `🎉 ${itemTitle} 실행이 완료되었습니다!`);
            
            // 연쇄상호작용이 가능한 항목인지 체크
            if (selectedItem.hasChainInteraction) {
                setTimeout(() => {
                    showChainInteractionInterface(progressMessages, itemTitle, tabTitle);
                }, 1000);
            }
        }, 1500 + (selectedItem.details.length * 800) + 1000);
    }
}

// 연쇄상호작용 인터페이스 표시
function showChainInteractionInterface(container, itemTitle, tabTitle) {
    const chainInteractionArea = document.createElement('div');
    chainInteractionArea.className = 'chain-interaction-area';
    chainInteractionArea.innerHTML = `
        <div class="chain-interaction-header">
            <h4>🔗 연쇄상호작용 시스템</h4>
            <p>${itemTitle}에 대한 유사한 상황의 학생들에게 동시 피드백을 진행할 수 있습니다.</p>
        </div>
        <div class="chain-interaction-controls">
            <div class="condition-status" id="conditionStatus">
                <span class="status-indicator">⚠️</span>
                <span class="status-text">조건 미설정</span>
                <button class="condition-setup-btn" onclick="setupConditions('${itemTitle}', '${tabTitle}')">조건 설정</button>
            </div>
            <div class="student-search-area" id="studentSearchArea" style="display: none;">
                <div class="search-controls">
                    <input type="text" placeholder="학생 검색..." class="student-search-input" id="studentSearchInput">
                    <button class="search-btn" onclick="searchStudents()">검색</button>
                </div>
                <div class="student-list" id="studentList">
                    <!-- 학생 목록이 여기에 표시됩니다 -->
                </div>
                <div class="execution-controls">
                    <button class="execute-btn" onclick="executeChainInteraction('${itemTitle}', '${tabTitle}')" disabled id="executeBtn">실행</button>
                    <button class="skip-btn" onclick="skipChainInteraction()">Skip</button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(chainInteractionArea);
    container.scrollTop = container.scrollHeight;
}

// 조건 설정 (팝업으로 연결 예정)
function setupConditions(itemTitle, tabTitle) {
    // 임시로 조건이 설정된 것으로 처리 (실제로는 팝업으로 연결)
    const conditionStatus = document.getElementById('conditionStatus');
    const studentSearchArea = document.getElementById('studentSearchArea');
    
    conditionStatus.innerHTML = `
        <span class="status-indicator">✅</span>
        <span class="status-text">조건 설정됨</span>
        <button class="condition-setup-btn" onclick="setupConditions('${itemTitle}', '${tabTitle}')">조건 수정</button>
    `;
    
    studentSearchArea.style.display = 'block';
    
    // 임시 학생 데이터로 자동 검색 실행
    setTimeout(() => {
        autoSearchStudents(itemTitle);
    }, 500);
}

// 학생 검색 (DB 연결 예정)
function searchStudents() {
    const searchInput = document.getElementById('studentSearchInput');
    const searchTerm = searchInput.value.trim();
    
    if (searchTerm) {
        displayStudentList(searchTerm);
    }
}

// 자동 학생 검색 (체험용)
function autoSearchStudents(itemTitle) {
    const studentList = document.getElementById('studentList');
    const executeBtn = document.getElementById('executeBtn');
    
    // 임시 학생 데이터
    const sampleStudents = [
        { name: '김학생', grade: '고2', similarity: '85%', status: '유사패턴' },
        { name: '이학생', grade: '고2', similarity: '78%', status: '유사패턴' },
        { name: '박학생', grade: '고1', similarity: '72%', status: '부분유사' }
    ];
    
    studentList.innerHTML = `
        <div class="student-list-header">
            <h5>유사 패턴 학생 목록 (${sampleStudents.length}명)</h5>
        </div>
        <div class="student-items">
            ${sampleStudents.map(student => `
                <div class="student-item">
                    <div class="student-info">
                        <span class="student-name">${student.name}</span>
                        <span class="student-grade">${student.grade}</span>
                    </div>
                    <div class="student-stats">
                        <span class="similarity">${student.similarity}</span>
                        <span class="status ${student.status === '유사패턴' ? 'similar' : 'partial'}">${student.status}</span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    executeBtn.disabled = false;
}

// 학생 목록 표시
function displayStudentList(searchTerm) {
    // DB 검색 결과를 표시하는 로직 (추후 구현)
    autoSearchStudents(searchTerm); // 임시로 자동 검색 사용
}

// 연쇄상호작용 실행
function executeChainInteraction(itemTitle, tabTitle) {
    const studentList = document.getElementById('studentList');
    const executeBtn = document.getElementById('executeBtn');
    
    // 실행 중 상태로 변경
    executeBtn.textContent = '실행 중...';
    executeBtn.disabled = true;
    
    // 실행 결과 표시
    setTimeout(() => {
        const resultArea = document.createElement('div');
        resultArea.className = 'execution-result';
        resultArea.innerHTML = `
            <div class="result-header">
                <h5>🎉 연쇄상호작용 실행 완료</h5>
            </div>
            <div class="result-details">
                <p>✓ 3명의 학생에게 메시지 발송 완료</p>
                <p>✓ 개별 맞춤 피드백 전달</p>
            </div>
            <div class="follow-up-area">
                <div class="follow-up-status">
                    <span class="status-indicator">⚠️</span>
                    <span class="status-text">추가상호작용, 추적계획 없음</span>
                    <button class="follow-up-btn" onclick="setupFollowUp('${itemTitle}', '${tabTitle}')">후속 상호작용 설정</button>
                </div>
                <button class="skip-btn" onclick="skipFollowUp()">Skip</button>
            </div>
        `;
        
        studentList.appendChild(resultArea);
        studentList.scrollTop = studentList.scrollHeight;
    }, 2000);
}

// Skip 연쇄상호작용
function skipChainInteraction() {
    const chainInteractionArea = document.querySelector('.chain-interaction-area');
    if (chainInteractionArea) {
        chainInteractionArea.style.opacity = '0.5';
        
        const skipMessage = document.createElement('div');
        skipMessage.className = 'skip-message';
        skipMessage.innerHTML = `
            <p>⏭️ 연쇄상호작용을 건너뛰었습니다.</p>
            <p>언제든 다시 실행할 수 있습니다.</p>
        `;
        
        chainInteractionArea.appendChild(skipMessage);
    }
}

// 후속 상호작용 설정
function setupFollowUp(itemTitle, tabTitle) {
    // 임시로 설정된 것으로 처리 (실제로는 별도 설정 화면으로 연결)
    const followUpStatus = document.querySelector('.follow-up-status');
    
    followUpStatus.innerHTML = `
        <span class="status-indicator">✅</span>
        <span class="status-text">후속 상호작용 설정됨</span>
        <button class="follow-up-btn" onclick="setupFollowUp('${itemTitle}', '${tabTitle}')">설정 수정</button>
    `;
    
    setTimeout(() => {
        const followUpArea = document.querySelector('.follow-up-area');
        const details = document.createElement('div');
        details.className = 'follow-up-details';
        details.innerHTML = `
            <div class="follow-up-schedule">
                <h6>📅 설정된 후속 상호작용</h6>
                <ul>
                    <li>1일 후: 학습 진도 체크</li>
                    <li>3일 후: 성과 평가</li>
                    <li>1주 후: 종합 리뷰</li>
                </ul>
            </div>
        `;
        followUpArea.appendChild(details);
    }, 500);
}

// Skip 후속 상호작용
function skipFollowUp() {
    const followUpArea = document.querySelector('.follow-up-area');
    if (followUpArea) {
        followUpArea.style.opacity = '0.5';
        
        const skipMessage = document.createElement('div');
        skipMessage.className = 'skip-message';
        skipMessage.innerHTML = `<p>⏭️ 후속 상호작용 설정을 건너뛰었습니다.</p>`;
        
        followUpArea.appendChild(skipMessage);
    }
}

function addProgressMessage(container, message) {
    const messageElement = document.createElement('div');
    messageElement.className = 'progress-message';
    messageElement.textContent = message;
    container.appendChild(messageElement);
    container.scrollTop = container.scrollHeight;
}

// 온보딩용 진행 메시지 추가 함수
function addOnboardingProgressMessage(container, message) {
    const messageElement = document.createElement('div');
    messageElement.className = 'onboarding-progress-message';
    messageElement.textContent = message;
    container.appendChild(messageElement);
    container.scrollTop = container.scrollHeight;
    
    // 채팅 컨테이너도 스크롤
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// ==================== 채팅 모드 ====================
function showChatInterface() {
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.innerHTML = `
        <div class="chat-test-notice">
            <h2>💬 채팅 기능</h2>
            <p>이 기능은 DB에 대한 심층적인 상호작용을 위해 준비되었습니다.</p>
            <p>현재는 개발 중이며, 테스트 페이지로 이동하여 기능을 확인하실 수 있습니다.</p>
            <div class="test-actions">
                <button class="test-button" onclick="goToTestPage()">테스트 페이지로 이동</button>
                <button class="test-button secondary" onclick="showChatPreview()">미리보기</button>
            </div>
        </div>
    `;
}

function goToTestPage() {
    // 테스트 페이지로 이동하는 로직
    alert('테스트 페이지로 이동합니다. (현재는 개발 중)');
}

function showChatPreview() {
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.innerHTML = `
        <div class="chat-preview">
            <h3>채팅 기능 미리보기</h3>
            <div class="preview-messages">
                <div class="preview-message ai">
                    <div class="message-avatar">🤖</div>
                    <div class="message-content">
                        <div class="message-text">안녕하세요! 무엇을 도와드릴까요?</div>
                    </div>
                </div>
                <div class="preview-message user">
                    <div class="message-avatar">👤</div>
                    <div class="message-content">
                        <div class="message-text">학습 데이터를 분석해주세요.</div>
                    </div>
                </div>
                <div class="preview-message ai">
                    <div class="message-avatar">🤖</div>
                    <div class="message-content">
                        <div class="message-text">학습 데이터를 분석하고 있습니다...</div>
                    </div>
                </div>
            </div>
            <p class="preview-note">* 이는 미리보기이며, 실제 기능은 개발 중입니다.</p>
            <button class="test-button" onclick="showChatInterface()">뒤로 가기</button>
        </div>
    `;
}

// ==================== 채팅 기능 ====================
function clearChat() {
    const chatContainer = document.getElementById('chatContainer');
    chatContainer.innerHTML = '';
}

function addMessage(sender, message) {
    const chatContainer = document.getElementById('chatContainer');
    const messageElement = document.createElement('div');
    messageElement.className = `message ${sender}`;
    messageElement.id = `message-${Date.now()}`;
    
    const avatar = sender === 'user' ? '👤' : (agents[currentCategory]?.avatar || '🤖');
    
    messageElement.innerHTML = `
        <div class="message-avatar">${avatar}</div>
        <div class="message-content">
            <div class="message-text">${message}</div>
            <div class="message-time">${new Date().toLocaleTimeString()}</div>
        </div>
    `;
    
    chatContainer.appendChild(messageElement);
    chatContainer.scrollTop = chatContainer.scrollHeight;
    
    return messageElement.id;
}

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (message) {
        addMessage('user', message);
        messageInput.value = '';
        
        // AI 응답 시뮬레이션
        setTimeout(() => {
            let response = '죄송합니다. 현재 채팅 기능은 개발 중입니다. 테스트 페이지를 이용해주세요.';
            addMessage('ai', response);
        }, 1000);
    }
}

// ==================== 검색 기능 ====================
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const categories = document.querySelectorAll('.menu-category');
        
        categories.forEach(category => {
            const title = category.querySelector('.category-title').textContent.toLowerCase();
            if (title.includes(searchTerm)) {
                category.style.display = 'block';
            } else {
                category.style.display = searchTerm === '' ? 'block' : 'none';
            }
        });
    });
}

// ==================== 초기화 ====================
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    
    // URL 파라미터로부터 상태 복원 시도
    restoreFromUrlParams();
    
    // 저장된 상태 복원 시도
    restoreState();
    
    // 기본 모드 설정 (상태 복원이 없으면)
    if (!currentMode) {
        switchMode('onboarding');
    }
    
    // Enter 키로 메시지 전송
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    console.log('교육 AI 시스템이 초기화되었습니다.');
});

// ==================== 이전 메뉴 버튼 기능 ====================
function addBackButton(text, callback) {
    const chatContainer = document.getElementById('chatContainer');
    const backButtonContainer = document.createElement('div');
    backButtonContainer.className = 'back-button-container';
    backButtonContainer.innerHTML = `
        <button class="onboarding-back-button" onclick="this.clickHandler()">${text}</button>
    `;
    
    // 클릭 핸들러 설정
    const button = backButtonContainer.querySelector('.onboarding-back-button');
    button.clickHandler = callback;
    
    chatContainer.appendChild(backButtonContainer);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// ==================== 플러그인 관리 함수 ====================
function showAddPluginMenu() {
    const modal = document.createElement('div');
    modal.className = 'add-card-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>플러그인 추가</h3>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <p>추가할 플러그인 유형을 선택하세요:</p>
                <div class="menu-options-grid">
                    ${pluginTypes.map(plugin => {
                        const isSelected = userSelectedPlugins.some(p => p.id === plugin.id);
                        return `
                            <div class="menu-option ${isSelected ? 'disabled' : ''}" 
                                 onclick="${isSelected ? '' : `addPlugin('${plugin.id}')`}">
                                <div class="option-icon">${plugin.icon}</div>
                                <div class="option-title">${plugin.title}</div>
                                ${isSelected ? '<div class="option-badge">추가됨</div>' : ''}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal() {
    const modal = document.querySelector('.add-card-modal');
    if (modal) modal.remove();
}

function addPlugin(pluginId) {
    const plugin = pluginTypes.find(p => p.id === pluginId);
    if (plugin && !userSelectedPlugins.some(p => p.id === pluginId)) {
        userSelectedPlugins.push(plugin);
        closeModal();
        // 현재 탭 새로고침
        const menuStructure = getMenuStructure();
        const categoryData = menuStructure[currentCategory];
        if (categoryData && categoryData.tabs.length > 0) {
            showSubmenuItems(categoryData.tabs[0]);
        }
    }
}

function deletePlugin(index) {
    userSelectedPlugins.splice(index, 1);
    // 현재 탭 새로고침
    const menuStructure = getMenuStructure();
    const categoryData = menuStructure[currentCategory];
    if (categoryData && categoryData.tabs.length > 0) {
        showSubmenuItems(categoryData.tabs[0]);
    }
}

function openPluginSettings(pluginId, customData = null) {
    const plugin = customData || userSelectedPlugins.find(p => p.id === pluginId);
    if (!plugin && !customData) return;
    
    // 내부링크 설정이면 바로 실행
    if (pluginId === 'internal_link') {
        executeInternalLink();
        return;
    }
    
    const settingsModal = document.createElement('div');
    settingsModal.className = 'settings-modal';
    settingsModal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${customData ? '🎨' : plugin.icon} ${customData ? customData.title : plugin.title} 설정</h3>
                <button class="modal-close" onclick="closeSettingsModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="settings-interface">
                    ${getPluginSettingsInterface(pluginId, customData)}
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeSettingsModal()">취소</button>
                <button class="btn-primary" onclick="savePluginSettings('${pluginId}', ${customData ? JSON.stringify(customData).replace(/"/g, '&quot;') : 'null'})">저장</button>
            </div>
        </div>
    `;
    document.body.appendChild(settingsModal);
}

function closeSettingsModal() {
    const modal = document.querySelector('.settings-modal');
    if (modal) modal.remove();
}

// 내부링크 실행 함수
function executeInternalLink() {
    // 현재 상태 저장
    saveCurrentState();
    
    // 예시: 학습 진도 페이지로 이동
    alert('내부 페이지로 이동합니다.');
    // 실제 구현 시: window.location.href = './progress.html';
}

function getPluginSettingsInterface(pluginId, customData) {
    // 각 플러그인 유형별 설정 인터페이스 반환
    const interfaces = {
        internal_link: `
            <h4>내부링크 설정</h4>
            <div class="form-group">
                <label>링크할 페이지</label>
                <select class="form-control" id="internalLinkTarget">
                    <option value="dashboard">메인 대시보드</option>
                    <option value="progress">학습 진도</option>
                    <option value="schedule">일정 관리</option>
                    <option value="results">평가 결과</option>
                </select>
            </div>
        `,
        external_link: `
            <h4>외부링크 설정</h4>
            <div class="form-group">
                <label>URL 주소</label>
                <input type="url" class="form-control" id="externalLinkUrl" placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label>열기 방식</label>
                <select class="form-control" id="externalLinkTarget">
                    <option value="_blank">새 탭에서 열기</option>
                    <option value="_self">현재 창에서 열기</option>
                </select>
            </div>
        `,
        custom_interface: customData ? `
            <h4>맞춤 인터페이스 - ${customData.title}</h4>
            <div class="form-group">
                <label>인터페이스 유형</label>
                <select class="form-control">
                    <option>기본 설정</option>
                    <option>고급 설정</option>
                    <option>사용자 정의</option>
                </select>
            </div>
            <div class="form-group">
                <label>레이아웃</label>
                <select class="form-control">
                    <option>그리드 형식</option>
                    <option>리스트 형식</option>
                    <option>카드 형식</option>
                </select>
            </div>
            <p class="text-muted">카테고리: ${customData.category}, 탭: ${customData.tabTitle}</p>
        ` : `
            <h4>맞춤 인터페이스 설정</h4>
            <div class="form-group">
                <label>인터페이스 유형</label>
                <select class="form-control">
                    <option>폼 생성</option>
                    <option>차트 생성</option>
                    <option>위젯 생성</option>
                    <option>테이블 생성</option>
                </select>
            </div>
            <div class="form-group">
                <label>레이아웃</label>
                <select class="form-control">
                    <option>그리드 형식</option>
                    <option>리스트 형식</option>
                    <option>카드 형식</option>
                </select>
            </div>
        `,
        send_message: `
            <h4>메시지 발송 설정</h4>
            <div class="form-group">
                <label>메시지 타입</label>
                <select class="form-control">
                    <option>알림 메시지</option>
                    <option>안내 메시지</option>
                    <option>경고 메시지</option>
                </select>
            </div>
            <div class="form-group">
                <label>메시지 내용</label>
                <textarea class="form-control" rows="3" placeholder="메시지 내용을 입력하세요"></textarea>
            </div>
            <div class="form-group">
                <label>발송 시간</label>
                <select class="form-control">
                    <option>즉시 발송</option>
                    <option>예약 발송</option>
                </select>
            </div>
        `,
        bulk_message: `
            <h4>유사 메시지 함께 발송 설정</h4>
            <div class="form-group">
                <label>기준 메시지</label>
                <textarea class="form-control" rows="2" placeholder="기준 메시지"></textarea>
            </div>
            <div class="form-group">
                <label>유사도 기준</label>
                <input type="range" min="50" max="100" value="80">
                <small class="text-muted">80% 이상 유사한 메시지 포함</small>
            </div>
            <div class="form-group">
                <label>최대 발송 수</label>
                <input type="number" class="form-control" value="10" min="1" max="50">
            </div>
        `,
        reminder: `
            <h4>학습 리마인더 설정</h4>
            <div class="form-group">
                <label>리마인더 유형</label>
                <select class="form-control">
                    <option>과제 리마인더</option>
                    <option>복습 리마인더</option>
                    <option>시험 리마인더</option>
                </select>
            </div>
            <div class="form-group">
                <label>알림 시간</label>
                <input type="time" class="form-control">
            </div>
            <div class="form-group">
                <label>반복 주기</label>
                <select class="form-control">
                    <option>매일</option>
                    <option>주 3회</option>
                    <option>주 1회</option>
                </select>
            </div>
        `,
        progress_visual: `
            <h4>진도 시각화 설정</h4>
            <div class="form-group">
                <label>차트 유형</label>
                <select class="form-control">
                    <option>원형 그래프</option>
                    <option>막대 그래프</option>
                    <option>라인 그래프</option>
                    <option>히트맵</option>
                </select>
            </div>
            <div class="form-group">
                <label>표시 기간</label>
                <select class="form-control">
                    <option>일일</option>
                    <option>주간</option>
                    <option>월간</option>
                    <option>분기별</option>
                </select>
            </div>
            <div class="form-group">
                <label>색상 테마</label>
                <select class="form-control">
                    <option>기본 색상</option>
                    <option>녹색 계열</option>
                    <option>파란색 계열</option>
                </select>
            </div>
        `,
        feedback_card: `
            <h4>피드백 수집 카드 설정</h4>
            <div class="form-group">
                <label>피드백 유형</label>
                <select class="form-control">
                    <option>별점 평가</option>
                    <option>텍스트 피드백</option>
                    <option>객관식 설문</option>
                    <option>주관식 설문</option>
                </select>
            </div>
            <div class="form-group">
                <label>질문 제목</label>
                <input type="text" class="form-control" placeholder="예: 오늘 수업은 어땠셨나요?">
            </div>
            <div class="form-group">
                <label>필수 응답 여부</label>
                <input type="checkbox" id="required"> <label for="required">필수 응답</label>
            </div>
        `,
        mood_checkin: `
            <h4>학습 기분 체크인 설정</h4>
            <div class="form-group">
                <label>체크인 빈도</label>
                <select class="form-control">
                    <option>수업 시작 시</option>
                    <option>수업 종료 시</option>
                    <option>수업 중간</option>
                </select>
            </div>
            <div class="form-group">
                <label>기분 선택 옵션</label>
                <div>
                    <input type="checkbox" id="happy" checked> <label for="happy">행복 😊</label><br>
                    <input type="checkbox" id="neutral" checked> <label for="neutral">보통 😐</label><br>
                    <input type="checkbox" id="sad" checked> <label for="sad">슬픔 😢</label><br>
                    <input type="checkbox" id="confused" checked> <label for="confused">혼란 😕</label>
                </div>
            </div>
        `,
        interaction_history: `
            <h4>상호작용 히스토리 카드 설정</h4>
            <div class="form-group">
                <label>표시 항목</label>
                <select class="form-control" multiple size="4">
                    <option selected>질문 횟수</option>
                    <option selected>답변 횟수</option>
                    <option>학습 시간</option>
                    <option>피드백 내용</option>
                </select>
            </div>
            <div class="form-group">
                <label>표시 기간</label>
                <select class="form-control">
                    <option>최근 1주</option>
                    <option>최근 1개월</option>
                    <option>전체 기간</option>
                </select>
            </div>
        `,
        strategy_recommender: `
            <h4>학습 전략 추천기 설정</h4>
            <div class="form-group">
                <label>학습 성향 분석</label>
                <select class="form-control">
                    <option>자동 분석</option>
                    <option>설문 기반</option>
                    <option>학습 데이터 기반</option>
                </select>
            </div>
            <div class="form-group">
                <label>추천 빈도</label>
                <select class="form-control">
                    <option>주 1회</option>
                    <option>월 2회</option>
                    <option>필요 시 마다</option>
                </select>
            </div>
            <div class="form-group">
                <label>추천 범위</label>
                <div>
                    <input type="checkbox" id="time" checked> <label for="time">시간 관리</label><br>
                    <input type="checkbox" id="method" checked> <label for="method">학습 방법</label><br>
                    <input type="checkbox" id="resource" checked> <label for="resource">학습 자료</label>
                </div>
            </div>
        `
    };
    
    return interfaces[pluginId] || '<p>플러그인 설정 인터페이스를 준비 중입니다.</p>';
}

function savePluginSettings(pluginId, customData) {
    // 플러그인별 처리
    if (pluginId === 'external_link') {
        const url = document.getElementById('externalLinkUrl')?.value;
        const target = document.getElementById('externalLinkTarget')?.value;
        
        if (url) {
            window.open(url, target);
            closeSettingsModal();
            return;
        }
    } else if (pluginId === 'internal_link') {
        const target = document.getElementById('internalLinkTarget')?.value;
        // 실제 내부 페이지로 이동
        saveCurrentState();
        alert(`${target} 페이지로 이동합니다.`);
        closeSettingsModal();
        return;
    }
    
    // 기본 처리
    const parsedData = customData && customData !== 'null' ? JSON.parse(customData.replace(/&quot;/g, '"')) : null;
    if (parsedData) {
        alert(`${parsedData.title} 맞춤 인터페이스 설정이 저장되었습니다.`);
    } else {
        const plugin = pluginTypes.find(p => p.id === pluginId);
        alert(`${plugin ? plugin.title : pluginId} 설정이 저장되었습니다.`);
    }
    closeSettingsModal();
}

// ==================== 상태 관리 및 네비게이션 ====================
function saveCurrentState() {
    const state = {
        category: currentCategory,
        mode: currentMode,
        tab: currentTab,
        item: currentItem,
        step: currentStep,
        scrollPosition: window.scrollY,
        timestamp: Date.now()
    };
    sessionStorage.setItem('navigationState', JSON.stringify(state));
}

function getCurrentState() {
    return {
        category: currentCategory,
        mode: currentMode,
        tab: currentTab,
        item: currentItem,
        step: currentStep
    };
}

function restoreState() {
    const savedState = sessionStorage.getItem('navigationState');
    if (savedState) {
        const state = JSON.parse(savedState);
        
        // 상태 복원
        if (state.category) {
            selectCategory(state.category);
        }
        if (state.mode) {
            switchMode(state.mode);
        }
        
        // 스크롤 위치 복원
        if (state.scrollPosition) {
            setTimeout(() => {
                window.scrollTo(0, state.scrollPosition);
            }, 100);
        }
        
        // 상태 초기화
        sessionStorage.removeItem('navigationState');
    }
}

// URL 파라미터로부터 상태 복원
function restoreFromUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const returnState = urlParams.get('returnState');
    
    if (returnState) {
        try {
            const state = JSON.parse(decodeURIComponent(returnState));
            
            // 상태 복원
            if (state.category) {
                currentCategory = state.category;
                selectCategory(state.category);
            }
            if (state.mode) {
                currentMode = state.mode;
                switchMode(state.mode);
            }
            if (state.tab) {
                currentTab = state.tab;
            }
            if (state.item) {
                currentItem = state.item;
            }
            if (state.step) {
                currentStep = state.step;
            }
            
            // URL 파라미터 제거
            window.history.replaceState({}, document.title, window.location.pathname);
        } catch (e) {
            console.error('상태 복원 오류:', e);
        }
    }
}

// ==================== 사용자 문맥정보 관리 ====================
function openContextSettings(itemTitle, tabTitle) {
    const contextModal = document.createElement('div');
    contextModal.className = 'context-modal';
    contextModal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>📋 ${itemTitle} - 사용자 문맥정보 관리</h3>
                <button class="modal-close" onclick="closeContextModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="context-tabs">
                    <button class="context-tab active" onclick="switchContextTab('required')">필수 정보</button>
                    <button class="context-tab" onclick="switchContextTab('additional')">추가 정보</button>
                </div>
                
                <div class="context-content" id="contextContent">
                    ${getContextForm(itemTitle, 'required')}
                </div>
                
                <div class="missing-info-notice" id="missingInfoNotice" style="display: none;">
                    <p>⚠️ 필수 정보가 누락되었습니다. 학생에게 정보 요청서가 자동 발송됩니다.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeContextModal()">취소</button>
                <button class="btn-primary" onclick="saveContextSettings('${itemTitle}', '${tabTitle}')">저장</button>
            </div>
        </div>
    `;
    document.body.appendChild(contextModal);
}

function getContextForm(itemTitle, type) {
    // 각 메뉴 항목별 필수/추가 정보 정의
    const contextFields = {
        '포모도르설정': {
            required: `
                <div class="form-group">
                    <label>현재 집중력 수준 <span class="required">*</span></label>
                    <select class="form-control" name="focus_level" required>
                        <option value="">선택하세요</option>
                        <option value="high">높음 (30분 이상 집중 가능)</option>
                        <option value="medium">보통 (15-30분 집중 가능)</option>
                        <option value="low">낮음 (15분 미만)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>주요 방해 요인 <span class="required">*</span></label>
                    <select class="form-control" name="distraction" required>
                        <option value="">선택하세요</option>
                        <option value="phone">휴대폰</option>
                        <option value="noise">소음</option>
                        <option value="thoughts">잡념</option>
                        <option value="fatigue">피로</option>
                    </select>
                </div>
            `,
            additional: `
                <div class="form-group">
                    <label>선호하는 휴식 방법</label>
                    <input type="text" class="form-control" name="break_preference" placeholder="예: 스트레칭, 음악 듣기">
                </div>
                <div class="form-group">
                    <label>최적 학습 시간대</label>
                    <select class="form-control" name="optimal_time">
                        <option value="morning">오전</option>
                        <option value="afternoon">오후</option>
                        <option value="evening">저녁</option>
                        <option value="night">밤</option>
                    </select>
                </div>
            `
        },
        '문제풀이 시작': {
            required: `
                <div class="form-group">
                    <label>문제 유형 <span class="required">*</span></label>
                    <select class="form-control" name="problem_type" required>
                        <option value="">선택하세요</option>
                        <option value="calculation">계산 문제</option>
                        <option value="proof">증명 문제</option>
                        <option value="application">응용 문제</option>
                        <option value="conceptual">개념 문제</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>현재 이해도 <span class="required">*</span></label>
                    <input type="range" class="form-control" name="understanding" min="1" max="10" value="5" required>
                    <small class="text-muted">1(전혀 모름) - 10(완벽히 이해)</small>
                </div>
            `,
            additional: `
                <div class="form-group">
                    <label>이전 실수 패턴</label>
                    <textarea class="form-control" name="mistake_pattern" rows="3" placeholder="자주 하는 실수나 어려워하는 부분"></textarea>
                </div>
                <div class="form-group">
                    <label>목표 시간</label>
                    <input type="number" class="form-control" name="target_time" placeholder="문제 해결 목표 시간(분)">
                </div>
            `
        },
        // 기본값
        default: {
            required: `
                <div class="form-group">
                    <label>학습 목표 <span class="required">*</span></label>
                    <input type="text" class="form-control" name="learning_goal" required placeholder="오늘의 학습 목표">
                </div>
                <div class="form-group">
                    <label>현재 상태 <span class="required">*</span></label>
                    <select class="form-control" name="current_state" required>
                        <option value="">선택하세요</option>
                        <option value="energetic">활기참</option>
                        <option value="normal">보통</option>
                        <option value="tired">피곤함</option>
                    </select>
                </div>
            `,
            additional: `
                <div class="form-group">
                    <label>추가 메모</label>
                    <textarea class="form-control" name="notes" rows="3" placeholder="기타 참고사항"></textarea>
                </div>
            `
        }
    };
    
    const fields = contextFields[itemTitle] || contextFields.default;
    return `
        <form id="contextForm">
            ${type === 'required' ? fields.required : fields.additional}
        </form>
    `;
}

function switchContextTab(tab) {
    // 탭 활성화 상태 변경
    document.querySelectorAll('.context-tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');
    
    // 폼 내용 변경
    const contextContent = document.getElementById('contextContent');
    const itemTitle = document.querySelector('.modal-header h3').textContent.split(' - ')[0].replace('📋 ', '');
    contextContent.innerHTML = getContextForm(itemTitle, tab);
}

function closeContextModal() {
    const modal = document.querySelector('.context-modal');
    if (modal) modal.remove();
}

function saveContextSettings(itemTitle, tabTitle) {
    const form = document.getElementById('contextForm');
    const formData = new FormData(form);
    
    // 필수 정보 확인
    const requiredFields = form.querySelectorAll('[required]');
    let missingRequired = false;
    
    requiredFields.forEach(field => {
        if (!field.value) {
            missingRequired = true;
            field.classList.add('error');
        }
    });
    
    if (missingRequired) {
        // 필수 정보 누락 시 알림 표시
        const notice = document.getElementById('missingInfoNotice');
        notice.style.display = 'block';
        
        // 2초 후 자동으로 요청서 발송 시뮬레이션
        setTimeout(() => {
            alert(`${itemTitle} 실행에 필요한 정보 요청서가 학생에게 발송되었습니다.`);
            closeContextModal();
        }, 2000);
    } else {
        // 정보 저장 성공
        alert(`${itemTitle}의 사용자 문맥정보가 저장되었습니다.`);
        closeContextModal();
    }
}

// ==================== 퍼스널 브랜딩 데이터 및 인터페이스 ====================
function getViralMarketingData() {
    return {
        title: '바이럴 마케팅',
        description: '바이럴 콘텐츠 제작 및 소셜미디어 마케팅 전략',
        tabs: [
            {
                id: 'blog',
                title: '블로그',
                description: '바이럴 블로그 콘텐츠 제작 및 SEO 전략',
                items: [
                    { title: '바이럴 포스트 작성', description: '화제성 있는 블로그 포스트 제작', details: ['트렌드 분석', '훅 제목 작성', '공유 유도 콘텐츠', '소셜 버튼 배치'] },
                    { title: '키워드 해킹', description: '검색량 높은 키워드 공략', details: ['키워드 트렌드 분석', '경쟁사 분석', '롱테일 키워드 발굴'] },
                    { title: '백링크 구축', description: '도메인 권위도 향상 전략', details: ['게스트 포스팅', '인플루언서 협업', '언론사 기고'] }
                ]
            },
            {
                id: 'youtube',
                title: '유튜브',
                description: '바이럴 유튜브 콘텐츠 제작 및 채널 성장',
                items: [
                    { title: '바이럴 영상 기획', description: '화제성 높은 유튜브 콘텐츠 기획', details: ['트렌드 리서치', '훅 시나리오', '클릭베이트 썸네일', '알고리즘 최적화'] },
                    { title: '쇼츠 제작', description: '유튜브 쇼츠 바이럴 전략', details: ['15초 훅', '트렌드 활용', '해시태그 최적화', '크로스 플랫폼 업로드'] },
                    { title: '구독자 폭증 전략', description: '채널 급성장 마케팅', details: ['콜라보 전략', '커뮤니티 포스트', '라이브 스트리밍', '구독 유도 기법'] }
                ]
            },
            {
                id: 'instagram',
                title: '인스타',
                description: '인스타그램 바이럴 마케팅 전략',
                items: [
                    { title: '바이럴 피드', description: '화제성 높은 피드 콘텐츠', details: ['트렌드 해시태그', '인플루언서 콜라보', '제품 배치', '인게이지먼트 유도'] },
                    { title: '스토리 해킹', description: '스토리 알고리즘 공략', details: ['인터랙티브 요소', '폴 기능 활용', '멀티슬라이드 전략'] },
                    { title: '릴스 바이럴', description: '릴스 폭발적 성장 전략', details: ['태그 챙린지', '소리 훅 전략', '크로스 플랫폼 확산'] }
                ]
            },
            {
                id: 'x',
                title: 'X (Twitter)',
                description: 'X 플랫폼 바이럴 마케팅',
                items: [
                    { title: '바이럴 트윗', description: '화제성 트윗 제작', details: ['핫트렌드 활용', '논란 마케팅', '리트윗 폭발 전략', '인플루언서 멘션'] },
                    { title: '스페이스 해킹', description: 'X 스페이스 활용 전략', details: ['라이브 참여', '실시간 소통', '네트워킹 효과'] },
                    { title: '해시태그 전쟁', description: '해시태그 바이럴 전략', details: ['핫태그 발굴', '트렌드 선점', '대중 심리 반영'] }
                ]
            },
            {
                id: 'threads',
                title: 'Threads',
                description: 'Threads 바이럴 전략',
                items: [
                    { title: '바이럴 스레드', description: '화제성 스레드 제작', details: ['고발성 주제', '논란 유발', '기대감 조성', '공유 유도'] },
                    { title: '인플루언서 타겨팅', description: '인플루언서 공략 전략', details: ['인플루언서 리서치', '멘션 전략', '콜라보 제안'] },
                    { title: '커뮤니티 폭발', description: '짧은 시간 내 커뮤니티 형성', details: ['이벤트 기획', '참여 유도', '바이럴 효과 증폭'] }
                ]
            }
        ]
    };
}

function showBrandingInterface() {
    const menuTabGrid = document.getElementById('menuTabGrid');
    const submenuContainer = document.getElementById('submenuContainer');
    
    // 퍼스널 브랜딩 전용 인터페이스
    menuTabGrid.innerHTML = `
        <div class="viral-interface">
            <div class="viral-header">
                <h2>🌟 퍼스널 브랜딩 전문가</h2>
                <p class="menu-description">개인 브랜드 구축 및 콘텐츠 전략 관리</p>
            </div>
            
            <!-- iframe 영역 -->
            <div class="iframe-container">
                <iframe 
                    src="https://mathking.kr/moodle/local/augmented_teacher/alt42/viralktm/index.html" 
                    frameborder="0" 
                    width="100%" 
                    height="400px"
                    id="brandingIframe">
                </iframe>
            </div>
            
            <!-- 플랫폼 탭 -->
            <div class="platform-tabs">
                <button class="platform-tab active" onclick="selectPlatformTab('blog')">
                    📝 블로그
                </button>
                <button class="platform-tab" onclick="selectPlatformTab('youtube')">
                    📺 유튜브
                </button>
                <button class="platform-tab" onclick="selectPlatformTab('instagram')">
                    📷 인스타
                </button>
                <button class="platform-tab" onclick="selectPlatformTab('x')">
                    🐦 X
                </button>
                <button class="platform-tab" onclick="selectPlatformTab('threads')">
                    🧵 Threads
                </button>
            </div>
        </div>
    `;
    
    // 기본으로 블로그 탭 표시
    selectPlatformTab('blog');
}

function selectPlatformTab(platform) {
    // 탭 활성화 상태 업데이트
    document.querySelectorAll('.platform-tab').forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // 플랫폼별 카드 표시
    showPlatformCards(platform);
}

function showPlatformCards(platform) {
    const submenuContainer = document.getElementById('submenuContainer');
    const viralData = getViralMarketingData();
    const platformData = viralData.tabs.find(tab => tab.id === platform);
    
    if (!platformData) return;
    
    submenuContainer.innerHTML = `
        <div class="platform-section">
            <h3>${platformData.title} 관리</h3>
            <p class="platform-description">${platformData.description}</p>
            
            <div class="menu-cards-container">
                <div class="menu-cards-grid">
                    ${platformData.items.map((item, index) => `
                        <div class="menu-card platform-card" onclick="executePlatformAction('${item.title}', '${platform}')">
                            <div class="card-icon">⚡</div>
                            <h4>${item.title}</h4>
                            <p class="card-description">${item.description}</p>
                        </div>
                    `).join('')}
                    
                    <!-- 플러그인 카드들 -->
                    ${userSelectedPlugins.map((plugin, index) => `
                        <div class="menu-card plugin-card" data-index="${index}" onclick="openPluginSettings('${plugin.id}')">
                            <button class="card-delete-btn" onclick="event.stopPropagation(); deletePlugin(${index})">×</button>
                            <div class="card-icon">${plugin.icon}</div>
                            <h4>${plugin.title}</h4>
                            <p class="card-description">${plugin.description}</p>
                        </div>
                    `).join('')}
                    
                    <!-- 플러그인 추가 카드 -->
                    <div class="menu-card add-card" onclick="showAddPluginMenu()">
                        <div class="add-icon">+</div>
                        <p>플러그인 추가</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function executePlatformAction(actionTitle, platform) {
    // 현재 상태 저장
    saveCurrentState();
    
    const submenuContainer = document.getElementById('submenuContainer');
    
    // 진행 상황 표시 영역 생성
    const progressId = `platformProgress_${Date.now()}`;
    const progressArea = document.createElement('div');
    progressArea.className = 'menu-progress-area';
    progressArea.innerHTML = `
        <h3>🚀 ${actionTitle} 실행 중...</h3>
        <div class="progress-messages" id="${progressId}"></div>
    `;
    
    submenuContainer.appendChild(progressArea);
    
    const progressMessages = document.getElementById(progressId);
    
    setTimeout(() => {
        addProgressMessage(progressMessages, `${actionTitle} 실행을 시작합니다...`);
    }, 500);
    
    // 플랫폼별 액션 데이터 찾기
    const viralData = getViralMarketingData();
    const platformData = viralData.tabs.find(tab => tab.id === platform);
    const actionData = platformData.items.find(item => item.title === actionTitle);
    
    if (actionData) {
        // 세부 작업들 순차 실행
        actionData.details.forEach((detail, index) => {
            setTimeout(() => {
                addProgressMessage(progressMessages, `✓ ${detail} - 완료`);
            }, 1500 + (index * 800));
        });
        
        // 완료 메시지
        setTimeout(() => {
            addProgressMessage(progressMessages, `🎉 ${actionTitle} 실행이 완료되었습니다!`);
        }, 1500 + (actionData.details.length * 800) + 1000);
    }
}

// ==================== 네비게이션 함수 ====================
function goToHome() {
    // 현재 상태 저장
    saveCurrentState();
    
    // 학생 홈으로 이동
    window.location.href = 'https://mathking.kr/moodle/local/augmented_teacher/alt42/studenthome/index.html';
}

// ==================== 전역 함수 노출 ===================="}
window.selectCategory = selectCategory;
window.switchMode = switchMode;
window.sendMessage = sendMessage;
window.selectMenuTab = selectMenuTab;
window.executeMenuAction = executeMenuAction;
window.goToTestPage = goToTestPage;
window.showChatPreview = showChatPreview;
window.showAddPluginMenu = showAddPluginMenu;
window.closeModal = closeModal;
window.addPlugin = addPlugin;
window.deletePlugin = deletePlugin;
window.openPluginSettings = openPluginSettings;
window.closeSettingsModal = closeSettingsModal;
window.savePluginSettings = savePluginSettings;
window.openCustomInterfaceForMenuItem = openCustomInterfaceForMenuItem;
window.openContextSettings = openContextSettings;
window.switchContextTab = switchContextTab;
window.closeContextModal = closeContextModal;
window.saveContextSettings = saveContextSettings;
window.saveCurrentState = saveCurrentState;
window.restoreState = restoreState;
window.goToHome = goToHome;