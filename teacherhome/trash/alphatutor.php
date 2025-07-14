import React, { useState, useEffect } from 'react';
import { Send, Phone, Video, MoreVertical, Paperclip, Smile, Mic, Search, Bell, Settings, Circle, Check, CheckCheck, MessageSquare, Grid, BarChart3, Users, BookOpen, Brain, TrendingUp, PieChart, Activity, FileText, Calendar, Target, Award, AlertTriangle, Zap, Sparkles, UserCheck, GraduationCap, Map, Radio, MessageCircle, Shield, Heart, Gauge, Focus, HelpCircle, Clock, BookmarkCheck, Pause, Play, Coffee, ChevronRight, ArrowRight, CheckCircle, Star, Timer, Eye, AlertCircle, Lightbulb, TrendingDown } from 'lucide-react';

const EducationAISystem = () => {
  const [messages, setMessages] = useState({});
  const [inputMessage, setInputMessage] = useState('');
  const [activeAgent, setActiveAgent] = useState('attendance');
  const [isTyping, setIsTyping] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [viewMode, setViewMode] = useState('onboarding'); // 'onboarding', 'menu', 'chat'
  const [selectedMenu, setSelectedMenu] = useState(null);
  const [onboardingStep, setOnboardingStep] = useState(0);
  const [showMenuCards, setShowMenuCards] = useState(false);

  const agents = [
    {
      id: 'attendance',
      name: '출결관리',
      status: 'online',
      avatar: <UserCheck className="w-6 h-6" />,
      role: '출석, 지각, 보강 및 위험 상황 관리',
      lastMessage: '김민수 학생 3회 연속 지각 - 상담 필요',
      lastTime: '방금',
      unread: 1,
      color: 'from-green-500 to-emerald-500',
      guide: '출결 상황을 종합적으로 관리하고 문제 상황을 조기에 발견합니다.',
      menus: [
        { id: 'late-management', icon: Clock, title: '지각관리', description: '지각 패턴 분석 및 개선 방안', color: 'from-orange-500 to-red-500' },
        { id: 'makeup-management', icon: BookmarkCheck, title: '보강관리', description: '결석자 보강 수업 스케줄링', color: 'from-blue-500 to-cyan-500' },
        { id: 'risk-management', icon: AlertTriangle, title: '위험관리', description: '출결 위험군 학생 조기 발견', color: 'from-red-500 to-pink-500' }
      ]
    },
    {
      id: 'learning',
      name: '학습진단',
      status: 'online',
      avatar: <GraduationCap className="w-6 h-6" />,
      role: '성적, 일정, 과제 및 도전 과제 관리',
      lastMessage: '중간고사 성적 분석 완료 - 평균 78점',
      lastTime: '2분 전',
      unread: 0,
      color: 'from-blue-500 to-indigo-500',
      guide: '학습 성과를 체계적으로 분석하고 개선점을 제시합니다.',
      menus: [
        { id: 'grade-management', icon: BarChart3, title: '성적관리', description: '성적 추이 분석 및 목표 설정', color: 'from-blue-500 to-indigo-500' },
        { id: 'schedule-management', icon: Calendar, title: '일정관리', description: '학습 스케줄 최적화', color: 'from-purple-500 to-violet-500' },
        { id: 'assignment-management', icon: FileText, title: '과제관리', description: '과제 제출 현황 및 피드백', color: 'from-green-500 to-teal-500' },
        { id: 'challenge-management', icon: Target, title: '도전관리', description: '도전 과제 및 특별 활동', color: 'from-orange-500 to-amber-500' }
      ]
    },
    {
      id: 'curriculum',
      name: '커리큘럼 진단',
      status: 'online',
      avatar: <Map className="w-6 h-6" />,
      role: '진도 관리 및 목표 설정',
      lastMessage: '이번 주 목표 달성률 85%',
      lastTime: '5분 전',
      unread: 2,
      color: 'from-purple-500 to-violet-500',
      guide: '교육과정 진행 상황을 모니터링하고 최적의 학습 경로를 제시합니다.',
      menus: [
        { id: 'progress-delay', icon: TrendingDown, title: '진도지연', description: '진도 지연 원인 분석 및 대책', color: 'from-red-500 to-rose-500' },
        { id: 'speed-delay', icon: Gauge, title: '속도지연', description: '학습 속도 개선 방안', color: 'from-yellow-500 to-orange-500' },
        { id: 'quarter-goal', icon: Target, title: '분기목표', description: '분기별 학습 목표 관리', color: 'from-blue-500 to-indigo-500' },
        { id: 'weekly-goal', icon: Calendar, title: '주간목표', description: '주별 세부 목표 설정', color: 'from-green-500 to-emerald-500' },
        { id: 'daily-goal', icon: CheckCircle, title: '오늘목표', description: '일일 학습 목표 체크', color: 'from-purple-500 to-pink-500' }
      ]
    },
    {
      id: 'activity',
      name: '현재활동 진단',
      status: 'online',
      avatar: <Activity className="w-6 h-6" />,
      role: '실시간 학습 활동 모니터링',
      lastMessage: '현재 집중도 92% - 우수',
      lastTime: '방금',
      unread: 0,
      color: 'from-orange-500 to-amber-500',
      guide: '현재 학습 상태를 실시간으로 모니터링하고 최적화합니다.',
      menus: [
        { id: 'calmness', icon: Eye, title: '침착도', description: '학습 중 심리적 안정성 측정', color: 'from-teal-500 to-cyan-500' },
        { id: 'score-management', icon: Star, title: '점수관리', description: '실시간 점수 및 랭킹 관리', color: 'from-yellow-500 to-amber-500' },
        { id: 'wrong-notes', icon: AlertCircle, title: '오답노트', description: '틀린 문제 분석 및 복습', color: 'from-red-500 to-orange-500' },
        { id: 'break-management', icon: Coffee, title: '휴식관리', description: '적절한 휴식 시간 관리', color: 'from-green-500 to-emerald-500' },
        { id: 'pomodoro', icon: Timer, title: '포모도르', description: '집중-휴식 사이클 관리', color: 'from-purple-500 to-violet-500' }
      ]
    },
    {
      id: 'communication',
      name: '소통진단',
      status: 'busy',
      avatar: <MessageCircle className="w-6 h-6" />,
      role: '상담 및 소통 관리',
      lastMessage: '이번 주 상담 예정 3건',
      lastTime: '10분 전',
      unread: 1,
      color: 'from-pink-500 to-rose-500',
      guide: '학생과의 효과적인 소통과 상담을 지원합니다.',
      menus: [
        { id: 'counseling-management', icon: Heart, title: '상담관리', description: '개별 상담 일정 및 기록 관리', color: 'from-pink-500 to-rose-500' },
        { id: 'counseling-app', icon: MessageSquare, title: '상담앱 활용', description: '상담 도구 및 앱 활용법', color: 'from-blue-500 to-indigo-500' },
        { id: 'counseling-delay', icon: Clock, title: '상담지연 관리', description: '상담 지연 시 대응 방안', color: 'from-orange-500 to-red-500' },
        { id: 'next-scenario', icon: ArrowRight, title: '다음 분기 시나리오', description: '향후 상담 계획 수립', color: 'from-purple-500 to-violet-500' }
      ]
    },
    {
      id: 'risk',
      name: '위험관리',
      status: 'online',
      avatar: <Shield className="w-6 h-6" />,
      role: '학습 위험 요소 조기 감지',
      lastMessage: '2명 학생 이탈 위험 감지',
      lastTime: '15분 전',
      unread: 2,
      color: 'from-red-500 to-rose-500',
      guide: '학습 과정에서 발생할 수 있는 위험 요소를 미리 감지합니다.',
      menus: [
        { id: 'dropout-detection', icon: AlertTriangle, title: '이탈감지', description: '학습 이탈 위험도 예측', color: 'from-red-500 to-rose-500' },
        { id: 'abnormal-pattern', icon: Activity, title: '이상패턴', description: '비정상적 학습 패턴 감지', color: 'from-orange-500 to-red-500' }
      ]
    },
    {
      id: 'dopamine',
      name: '도파민 균형',
      status: 'online',
      avatar: <Brain className="w-6 h-6" />,
      role: '학습 동기 및 보상 시스템 관리',
      lastMessage: '도파민 밸런스 양호',
      lastTime: '20분 전',
      unread: 0,
      color: 'from-indigo-500 to-purple-500',
      guide: '학습 동기를 지속시키는 도파민 시스템을 관리합니다.',
      menus: [
        { id: 'tonic-dopamine', icon: Gauge, title: '토닉 도파민', description: '기본 동기 수준 관리', color: 'from-indigo-500 to-purple-500' },
        { id: 'phasic-dopamine', icon: Zap, title: '페이직 도파민', description: '순간적 보상 시스템 관리', color: 'from-yellow-500 to-orange-500' }
      ]
    },
    {
      id: 'usage',
      name: '사용법 진단',
      status: 'online',
      avatar: <HelpCircle className="w-6 h-6" />,
      role: '시스템 활용 최적화',
      lastMessage: '활용도 개선 포인트 3개 발견',
      lastTime: '25분 전',
      unread: 0,
      color: 'from-gray-500 to-gray-600',
      guide: '시스템을 더욱 효과적으로 활용할 수 있도록 도움을 제공합니다.',
      menus: [
        { id: 'proficiency-management', icon: TrendingUp, title: '능숙도 관리', description: '시스템 사용 숙련도 향상', color: 'from-green-500 to-emerald-500' },
        { id: 'improvement-detection', icon: Lightbulb, title: '개선지점 포착', description: '효율성 개선 방안 제시', color: 'from-blue-500 to-indigo-500' }
      ]
    }
  ];

  // 온보딩 시나리오
  const onboardingScenarios = {
    attendance: [
      { type: 'agent', text: '안녕하세요! 출결관리 AI입니다. 학생들의 출석 상황을 체계적으로 관리해드립니다.' },
      { type: 'agent', text: '지각, 결석, 조퇴 등의 패턴을 분석하여 문제 상황을 미리 예방할 수 있어요.' },
      { type: 'agent', text: '예를 들어, 연속 지각이나 무단결석이 발생하면 즉시 알려드려서 적절한 조치를 취할 수 있습니다.' },
      { type: 'agent', text: '지금 사용 가능한 주요 기능들을 확인해보세요!' }
    ],
    learning: [
      { type: 'agent', text: '학습진단 AI가 인사드립니다! 학생들의 학습 성과를 종합적으로 분석합니다.' },
      { type: 'agent', text: '성적 추이, 과제 수행률, 학습 일정 등을 통해 개인별 맞춤 지도를 도와드려요.' },
      { type: 'agent', text: '특히 성적이 떨어지는 구간을 미리 감지하여 선제적 대응이 가능합니다.' },
      { type: 'agent', text: '다음 기능들로 더 효과적인 학습 관리를 시작해보세요!' }
    ],
    curriculum: [
      { type: 'agent', text: '커리큘럼 진단 AI입니다. 교육과정의 진행 상황을 최적화합니다.' },
      { type: 'agent', text: '진도 지연이나 속도 문제를 조기에 발견하고, 분기별/주별/일별 목표를 체계적으로 관리해요.' },
      { type: 'agent', text: '학급 전체의 이해도를 고려한 맞춤형 진도 계획을 수립할 수 있습니다.' },
      { type: 'agent', text: '지금 바로 시작할 수 있는 관리 도구들입니다!' }
    ]
  };

  // 초기 메시지 설정
  useEffect(() => {
    const initialMessages = {};
    agents.forEach(agent => {
      initialMessages[agent.id] = [
        {
          id: 1,
          text: `${agent.name} 시스템입니다. ${agent.guide}`,
          sender: 'agent',
          time: '오전 9:00',
          read: true
        }
      ];
    });
    setMessages(initialMessages);
  }, []);

  // 온보딩 진행
  const proceedOnboarding = () => {
    const currentAgent = agents.find(a => a.id === activeAgent);
    const scenario = onboardingScenarios[activeAgent] || [];
    
    if (onboardingStep < scenario.length) {
      const message = scenario[onboardingStep];
      const newMessage = {
        id: Date.now(),
        text: message.text,
        sender: 'agent',
        time: new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' }),
        read: false
      };

      setMessages(prev => ({
        ...prev,
        [activeAgent]: [...(prev[activeAgent] || []), newMessage]
      }));

      setOnboardingStep(prev => prev + 1);

      // 마지막 단계에서 메뉴 표시
      if (onboardingStep === scenario.length - 1) {
        setTimeout(() => setShowMenuCards(true), 1000);
      }
    }
  };

  // 자동 온보딩 시작
  useEffect(() => {
    if (viewMode === 'onboarding') {
      setOnboardingStep(0);
      setShowMenuCards(false);
      setTimeout(() => proceedOnboarding(), 1000);
    }
  }, [activeAgent, viewMode]);

  useEffect(() => {
    if (viewMode === 'onboarding' && onboardingStep > 0) {
      const timer = setTimeout(() => proceedOnboarding(), 2000);
      return () => clearTimeout(timer);
    }
  }, [onboardingStep, viewMode]);

  const handleSendMessage = async () => {
    if (!inputMessage.trim()) return;

    const newMessage = {
      id: Date.now(),
      text: inputMessage,
      sender: 'teacher',
      time: new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' }),
      read: false
    };

    setMessages(prev => ({
      ...prev,
      [activeAgent]: [...(prev[activeAgent] || []), newMessage]
    }));
    
    const userQuery = inputMessage;
    setInputMessage('');
    setIsTyping(true);

    // AI 응답 및 추천 시스템
    setTimeout(() => {
      const agent = agents.find(a => a.id === activeAgent);
      let responseText = '';
      let recommendedAgent = null;
      
      // 키워드 기반 응답 및 추천
      if (userQuery.includes('지각') || userQuery.includes('결석')) {
        responseText = '지각 및 결석 패턴을 분석해보니, 김민수 학생이 주 3회 지각하고 있습니다. 상담이 필요해 보입니다.';
        if (activeAgent !== 'communication') {
          recommendedAgent = 'communication';
        }
      } else if (userQuery.includes('성적') || userQuery.includes('점수')) {
        responseText = '최근 성적 분석 결과, 중간고사 평균이 78점입니다. 수학 과목에서 특히 개선이 필요해 보입니다.';
        if (activeAgent !== 'learning') {
          recommendedAgent = 'learning';
        }
      } else if (userQuery.includes('진도') || userQuery.includes('커리큘럼')) {
        responseText = '현재 진도율은 85%로 계획보다 약간 빠릅니다. 이해도를 점검하여 적절한 속도 조절이 필요합니다.';
        if (activeAgent !== 'curriculum') {
          recommendedAgent = 'curriculum';
        }
      } else if (userQuery.includes('집중') || userQuery.includes('몰입')) {
        responseText = '현재 학급 전체 집중도는 92%입니다. 포모도르 기법을 활용하면 더 효과적일 것 같습니다.';
        if (activeAgent !== 'activity') {
          recommendedAgent = 'activity';
        }
      } else {
        // 기본 응답
        switch(agent.id) {
          case 'attendance':
            responseText = '출결 현황을 확인해보니 전체적으로 양호합니다. 특별히 관리가 필요한 학생은 3명입니다. 📊';
            break;
          case 'learning':
            responseText = '학습 진단 결과, 대부분의 학생들이 순조롭게 진행하고 있습니다. 개별 지도가 필요한 영역을 확인해보세요. 📈';
            break;
          case 'curriculum':
            responseText = '커리큘럼 진행 상황이 양호합니다. 다음 주 목표 달성을 위한 계획을 세워보겠습니다. 📚';
            break;
          case 'activity':
            responseText = '현재 활동 상태가 우수합니다. 지속적인 모니터링을 통해 최적화하겠습니다. 🎯';
            break;
          case 'communication':
            responseText = '소통 현황을 점검했습니다. 상담이 필요한 학생들의 우선순위를 정리해드릴게요. 💬';
            break;
          default:
            responseText = '분석을 완료했습니다. 추가로 필요한 정보가 있으면 말씀해주세요.';
        }
      }

      const aiResponse = {
        id: Date.now() + 1,
        text: responseText,
        sender: 'agent',
        time: new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' }),
        read: false
      };

      setMessages(prev => ({
        ...prev,
        [activeAgent]: [...(prev[activeAgent] || []), aiResponse]
      }));

      // 다른 에이전트 추천
      if (recommendedAgent) {
        setTimeout(() => {
          const recommendedAgentInfo = agents.find(a => a.id === recommendedAgent);
          const recommendMessage = {
            id: Date.now() + 2,
            text: `이 문제에 대해서는 "${recommendedAgentInfo.name}"와 상담하는 것이 더 도움될 것 같습니다. 바로 연결해드릴까요?`,
            sender: 'agent',
            time: new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' }),
            read: false,
            isRecommendation: true,
            recommendedAgent: recommendedAgent
          };

          setMessages(prev => ({
            ...prev,
            [activeAgent]: [...(prev[activeAgent] || []), recommendMessage]
          }));
        }, 1000);
      }

      setIsTyping(false);
    }, 1500);
  };

  const handleMenuClick = (menu) => {
    setSelectedMenu(menu);
    
    const menuMessage = {
      id: Date.now(),
      text: `"${menu.title}" 기능을 실행합니다.`,
      sender: 'agent',
      time: new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' }),
      read: false,
      isSystem: true
    };

    setMessages(prev => ({
      ...prev,
      [activeAgent]: [...(prev[activeAgent] || []), menuMessage]
    }));

    // 기능별 상세 응답
    setTimeout(() => {
      let detailResponse = '';
      switch(menu.id) {
        case 'late-management':
          detailResponse = '📊 지각 관리 분석 완료\n• 김민수: 3회 연속 지각 (상담 필요)\n• 이지수: 주 2회 지각 패턴 (모니터링)\n• 박준호: 개선 중 (격려 필요)';
          break;
        case 'grade-management':
          detailResponse = '📈 성적 관리 현황\n• 학급 평균: 78.5점 (전년 대비 +3.2점)\n• 상위 20%: 92점 이상\n• 개선 필요: 5명 (개별 지도 계획 수립)';
          break;
        case 'pomodoro':
          detailResponse = '⏰ 포모도르 기법 적용 결과\n• 집중 시간: 25분 → 35분 증가\n• 휴식 효율성: 87% 향상\n• 권장 사이클: 45분 집중 + 10분 휴식';
          break;
        default:
          detailResponse = `${menu.title} 분석이 완료되었습니다. 상세 데이터를 확인해보세요.`;
      }

      const detailMessage = {
        id: Date.now() + 1,
        text: detailResponse,
        sender: 'agent',
        time: new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' }),
        read: false
      };

      setMessages(prev => ({
        ...prev,
        [activeAgent]: [...(prev[activeAgent] || []), detailMessage]
      }));
    }, 1000);
  };

  const handleRecommendationAccept = (recommendedAgent) => {
    setActiveAgent(recommendedAgent);
    setViewMode('chat');
    
    // 전환 메시지
    const transitionMessage = {
      id: Date.now(),
      text: `${agents.find(a => a.id === recommendedAgent).name}으로 전환되었습니다. 어떻게 도와드릴까요?`,
      sender: 'agent',
      time: new Date().toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' }),
      read: false,
      isSystem: true
    };

    setMessages(prev => ({
      ...prev,
      [recommendedAgent]: [...(prev[recommendedAgent] || []), transitionMessage]
    }));
  };

  const getStatusColor = (status) => {
    switch(status) {
      case 'online': return 'bg-green-500';
      case 'busy': return 'bg-yellow-500';
      case 'offline': return 'bg-gray-400';
      default: return 'bg-gray-400';
    }
  };

  const currentAgent = agents.find(a => a.id === activeAgent);

  return (
    <div className="h-screen bg-gray-900 flex overflow-hidden">
      {/* 좌측 에이전트 목록 */}
      <div className="w-80 bg-gray-800 border-r border-gray-700 flex flex-col">
        <div className="p-4 border-b border-gray-700">
          <div className="flex items-center justify-between mb-4">
            <h1 className="text-xl font-bold text-white">교육 AI 시스템</h1>
            <div className="flex space-x-2">
              <button className="text-gray-400 hover:text-white transition-colors">
                <Bell className="w-5 h-5" />
              </button>
              <button className="text-gray-400 hover:text-white transition-colors">
                <Settings className="w-5 h-5" />
              </button>
            </div>
          </div>
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
            <input
              type="text"
              placeholder="에이전트 검색..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full bg-gray-700 text-white rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>
        </div>

        <div className="flex-1 overflow-y-auto">
          {agents.filter(agent => 
            agent.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            agent.role.toLowerCase().includes(searchQuery.toLowerCase())
          ).map(agent => (
            <div
              key={agent.id}
              onClick={() => setActiveAgent(agent.id)}
              className={`flex items-center p-4 hover:bg-gray-700 cursor-pointer transition-all ${
                activeAgent === agent.id ? 'bg-gray-700 border-l-4 border-purple-500' : ''
              }`}
            >
              <div className="relative mr-3">
                <div className={`w-12 h-12 rounded-full bg-gradient-to-br ${agent.color} flex items-center justify-center text-white`}>
                  {agent.avatar}
                </div>
                <div className={`absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-gray-800 ${getStatusColor(agent.status)}`} />
              </div>

              <div className="flex-1 min-w-0">
                <div className="flex items-center justify-between">
                  <h3 className="font-semibold text-white truncate">{agent.name}</h3>
                  <span className="text-xs text-gray-400">{agent.lastTime}</span>
                </div>
                <p className="text-xs text-gray-400 mb-1">{agent.role}</p>
                <p className="text-sm text-gray-300 truncate">{agent.lastMessage}</p>
              </div>

              {agent.unread > 0 && (
                <div className="ml-2 bg-purple-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                  {agent.unread}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* 우측 콘텐츠 영역 */}
      <div className="flex-1 flex flex-col bg-gray-850">
        {/* 헤더 */}
        <div className="bg-gray-800 border-b border-gray-700 p-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <div className="relative mr-3">
                <div className={`w-10 h-10 rounded-full bg-gradient-to-br ${currentAgent?.color} flex items-center justify-center text-white`}>
                  {currentAgent?.avatar}
                </div>
                <div className={`absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-gray-800 ${
                  getStatusColor(currentAgent?.status)
                }`} />
              </div>
              <div>
                <h2 className="font-semibold text-white">{currentAgent?.name}</h2>
                <p className="text-xs text-gray-400">{currentAgent?.role}</p>
              </div>
            </div>
            <div className="flex items-center space-x-3">
              {/* 모드 전환 버튼 */}
              <div className="flex bg-gray-700 rounded-lg p-1">
                <button
                  onClick={() => setViewMode('onboarding')}
                  className={`px-3 py-1 rounded flex items-center space-x-1 transition-all ${
                    viewMode === 'onboarding' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'
                  }`}
                >
                  <Lightbulb className="w-4 h-4" />
                  <span className="text-sm">온보딩</span>
                </button>
                <button
                  onClick={() => setViewMode('menu')}
                  className={`px-3 py-1 rounded flex items-center space-x-1 transition-all ${
                    viewMode === 'menu' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'
                  }`}
                >
                  <Grid className="w-4 h-4" />
                  <span className="text-sm">메뉴</span>
                </button>
                <button
                  onClick={() => setViewMode('chat')}
                  className={`px-3 py-1 rounded flex items-center space-x-1 transition-all ${
                    viewMode === 'chat' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'
                  }`}
                >
                  <MessageSquare className="w-4 h-4" />
                  <span className="text-sm">채팅</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* 콘텐츠 영역 */}
        {viewMode === 'onboarding' ? (
          <>
            {/* 온보딩 채팅 */}
            <div className="flex-1 overflow-y-auto p-4 bg-gray-850">
              <div className="max-w-3xl mx-auto space-y-4">
                {(messages[activeAgent] || []).map((message, index) => (
                  <div key={message.id} className="flex justify-start animate-fadeIn">
                    <div className="max-w-[80%]">
                      {message.isSystem && (
                        <div className="text-center text-xs text-gray-500 mb-2">
                          시스템 메시지
                        </div>
                      )}
                      <div className="bg-gray-700 text-white rounded-2xl rounded-bl-none px-4 py-3">
                        <p className="text-sm leading-relaxed">{message.text}</p>
                      </div>
                      <div className="flex items-center mt-1 justify-start">
                        <span className="text-xs text-gray-500">{message.time}</span>
                      </div>
                    </div>
                  </div>
                ))}
                
                {/* 메뉴 카드 표시 */}
                {showMenuCards && (
                  <div className="mt-6 animate-fadeIn">
                    <div className="text-center mb-4">
                      <p className="text-white font-semibold">사용 가능한 기능들</p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      {currentAgent?.menus.map(menu => (
                        <button
                          key={menu.id}
                          onClick={() => handleMenuClick(menu)}
                          className="group relative overflow-hidden rounded-lg p-4 bg-gray-800 border border-gray-700 hover:border-purple-500 transition-all duration-300 transform hover:scale-105"
                        >
                          <div className={`absolute inset-0 bg-gradient-to-br ${menu.color} opacity-0 group-hover:opacity-10 transition-opacity duration-300`} />
                          <div className="relative z-10 flex items-center space-x-3">
                            <div className={`w-8 h-8 rounded-lg bg-gradient-to-br ${menu.color} flex items-center justify-center`}>
                              <menu.icon className="w-4 h-4 text-white" />
                            </div>
                            <div className="text-left">
                              <h4 className="text-sm font-bold text-white">{menu.title}</h4>
                              <p className="text-xs text-gray-400">{menu.description}</p>
                            </div>
                            <ChevronRight className="w-4 h-4 text-gray-400 group-hover:text-white ml-auto" />
                          </div>
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          </>
        ) : viewMode === 'menu' ? (
          /* 메뉴 모드 */
          <div className="flex-1 overflow-y-auto p-4 bg-gray-850">
            <div className="max-w-4xl mx-auto">
              <h3 className="text-xl font-bold text-white mb-4">
                {currentAgent?.name} 관리 기능
              </h3>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                {currentAgent?.menus.map(menu => (
                  <button
                    key={menu.id}
                    onClick={() => handleMenuClick(menu)}
                    className={`group relative overflow-hidden rounded-lg p-4 bg-gray-800 border border-gray-700 hover:border-purple-500 transition-all duration-300 transform hover:scale-105 ${
                      selectedMenu?.id === menu.id ? 'ring-2 ring-purple-500' : ''
                    }`}
                  >
                    <div className={`absolute inset-0 bg-gradient-to-br ${menu.color} opacity-0 group-hover:opacity-10 transition-opacity duration-300`} />
                    <div className="relative z-10 text-center">
                      <div className={`w-10 h-10 rounded-lg bg-gradient-to-br ${menu.color} flex items-center justify-center mx-auto mb-2`}>
                        <menu.icon className="w-5 h-5 text-white" />
                      </div>
                      <h4 className="text-sm font-bold text-white mb-1">{menu.title}</h4>
                      <p className="text-xs text-gray-400">{menu.description}</p>
                    </div>
                  </button>
                ))}
              </div>

              {/* 요약 정보 */}
              <div className="mt-6 grid grid-cols-3 gap-4">
                <div className="bg-gray-800 rounded-lg p-4 border border-gray-700 text-center">
                  <p className="text-2xl font-bold text-green-400">92%</p>
                  <p className="text-sm text-gray-400">시스템 효율성</p>
                </div>
                <div className="bg-gray-800 rounded-lg p-4 border border-gray-700 text-center">
                  <p className="text-2xl font-bold text-blue-400">24</p>
                  <p className="text-sm text-gray-400">활성 학생</p>
                </div>
                <div className="bg-gray-800 rounded-lg p-4 border border-gray-700 text-center">
                  <p className="text-2xl font-bold text-orange-400">3</p>
                  <p className="text-sm text-gray-400">관심 필요</p>
                </div>
              </div>
            </div>
          </div>
        ) : (
          /* 채팅 모드 */
          <>
            {/* 가이드 메시지 */}
            <div className="bg-gradient-to-r from-purple-600 to-blue-600 p-3">
              <p className="text-white text-sm text-center">
                💡 {currentAgent?.guide} 무엇이든 자유롭게 질문해보세요!
              </p>
            </div>

            {/* 채팅 영역 */}
            <div className="flex-1 overflow-y-auto p-4 bg-gray-850">
              <div className="max-w-3xl mx-auto space-y-4">
                {(messages[activeAgent] || []).map((message) => (
                  <div
                    key={message.id}
                    className={`flex ${message.sender === 'teacher' ? 'justify-end' : 'justify-start'} animate-fadeIn`}
                  >
                    <div className={`max-w-[80%] ${message.sender === 'teacher' ? 'order-2' : 'order-1'}`}>
                      {message.isSystem && (
                        <div className="text-center text-xs text-gray-500 mb-2">
                          시스템 알림
                        </div>
                      )}
                      <div className={`rounded-2xl px-4 py-3 ${
                        message.sender === 'teacher'
                          ? 'bg-purple-600 text-white rounded-br-none'
                          : 'bg-gray-700 text-white rounded-bl-none'
                      }`}>
                        <p className="text-sm leading-relaxed whitespace-pre-line">{message.text}</p>
                        {message.isRecommendation && (
                          <div className="mt-3 pt-3 border-t border-gray-600">
                            <button
                              onClick={() => handleRecommendationAccept(message.recommendedAgent)}
                              className="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded-full text-xs transition-all"
                            >
                              네, 연결해주세요 →
                            </button>
                          </div>
                        )}
                      </div>
                      <div className={`flex items-center mt-1 space-x-2 ${
                        message.sender === 'teacher' ? 'justify-end' : 'justify-start'
                      }`}>
                        <span className="text-xs text-gray-500">{message.time}</span>
                        {message.sender === 'teacher' && (
                          <span className="text-gray-500">
                            {message.read ? <CheckCheck className="w-3 h-3" /> : <Check className="w-3 h-3" />}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
                {isTyping && (
                  <div className="flex justify-start animate-fadeIn">
                    <div className="bg-gray-700 rounded-2xl rounded-bl-none px-4 py-3">
                      <div className="flex space-x-2">
                        <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                        <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                        <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* 입력 영역 */}
            <div className="bg-gray-800 border-t border-gray-700 p-4">
              <div className="max-w-3xl mx-auto">
                <div className="flex items-center space-x-3">
                  <button className="text-gray-400 hover:text-white transition-colors">
                    <Paperclip className="w-5 h-5" />
                  </button>
                  <div className="flex-1 relative">
                    <input
                      type="text"
                      value={inputMessage}
                      onChange={(e) => setInputMessage(e.target.value)}
                      onKeyPress={(e) => e.key === 'Enter' && handleSendMessage()}
                      placeholder="궁금한 것을 자유롭게 물어보세요..."
                      className="w-full bg-gray-700 text-white rounded-full px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                    <button className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                      <Smile className="w-5 h-5" />
                    </button>
                  </div>
                  <button className="text-gray-400 hover:text-white transition-colors">
                    <Mic className="w-5 h-5" />
                  </button>
                  <button
                    onClick={handleSendMessage}
                    className="bg-purple-600 hover:bg-purple-700 text-white rounded-full p-3 transition-all transform hover:scale-110"
                  >
                    <Send className="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>
          </>
        )}
      </div>

      <style jsx>{`
        @keyframes fadeIn {
          from {
            opacity: 0;
            transform: translateY(10px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
        .animate-fadeIn {
          animation: fadeIn 0.3s ease-out;
        }
        .bg-gray-850 {
          background-color: #1a1b23;
        }
      `}</style>
    </div>
  );
};

export default EducationAISystem;