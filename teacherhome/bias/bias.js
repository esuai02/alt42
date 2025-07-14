/**
 * 인지관성 개선 모듈
 */

// 인지관성 개선 데이터 구조
const biasData = {
    title: '인지관성 개선',
    description: '학생들의 인지관성을 개선하고 연쇄상호작용을 통해 효과적인 학습 환경을 조성합니다.',
    tabs: [
        {
            id: 'concept_study',
            title: '개념공부',
            description: '인지관성 유형화를 통한 개념 학습',
            explanation: '학생의 인지관성 유형에 따른 개념 학습 지원을 제공합니다.',
            items: [
                {
                    title: '포모도르설정',
                    description: '집중력 향상을 위한 포모도르 기법을 설정합니다.',
                    details: ['집중 시간 설정', '휴식 시간 조정', '개인별 최적화', '진행 상황 모니터링'],
                    hasChainInteraction: true
                },
                {
                    title: '개념노트 사용법',
                    description: '효과적인 개념 정리를 위한 노트 작성법을 안내합니다.',
                    details: ['구조화된 노트 작성', '핵심 개념 추출', '연관 관계 매핑', '복습 체계 구축'],
                    hasChainInteraction: true
                },
                {
                    title: '음성대화 사용법',
                    description: 'AI와의 음성 대화를 통한 개념 학습을 지원합니다.',
                    details: ['음성 인식 설정', '대화 모드 선택', '질문 기법 안내', '피드백 활용법'],
                    hasChainInteraction: true
                },
                {
                    title: '테스트 응시방법',
                    description: '개념 이해도 테스트 응시 방법을 안내합니다.',
                    details: ['테스트 준비', '응시 전략', '시간 관리', '결과 분석'],
                    hasChainInteraction: true
                },
                {
                    title: '질의응답 및 지면평가',
                    description: '개념 학습에 대한 질의응답 및 평가를 진행합니다.',
                    details: ['질문 유형 분류', '답변 품질 평가', '이해도 측정', '개선점 도출'],
                    hasChainInteraction: true
                }
            ]
        },
        {
            id: 'problem_solving',
            title: '문제풀이',
            description: '인지관성 유형화를 통한 문제 해결',
            explanation: '학생의 문제 해결 패턴을 분석하여 최적화된 풀이 과정을 제공합니다.',
            items: [
                {
                    title: '문제풀이 시작',
                    description: '효과적인 문제 풀이 시작 전략을 제공합니다.',
                    details: ['문제 분석 기법', '접근 전략 수립', '필요 도구 준비', '목표 설정'],
                    hasChainInteraction: true
                },
                {
                    title: '문제풀이 과정',
                    description: '체계적인 문제 해결 과정을 안내합니다.',
                    details: ['단계별 접근', '중간 점검', '방향 수정', '효율성 향상'],
                    hasChainInteraction: true
                },
                {
                    title: '문제풀이 마무리',
                    description: '문제 해결 후 검토 및 정리 과정을 지원합니다.',
                    details: ['답안 검증', '과정 정리', '학습 내용 정리', '다음 단계 계획'],
                    hasChainInteraction: true
                }
            ]
        },
        {
            id: 'learning_management',
            title: '학습관리',
            description: '인지관성 유형화를 통한 학습 관리',
            explanation: '개인의 학습 패턴에 맞는 관리 시스템을 제공합니다.',
            items: [
                {
                    title: '내공부방',
                    description: '개인 학습 공간 관리 및 최적화를 지원합니다.',
                    details: ['학습 환경 설정', '자료 정리 시스템', '집중력 향상 방법', '효율성 개선'],
                    hasChainInteraction: true
                },
                {
                    title: '공부결과',
                    description: '학습 성과를 분석하고 피드백을 제공합니다.',
                    details: ['성과 측정', '진도 분석', '강약점 파악', '개선 방안 제시'],
                    hasChainInteraction: true
                },
                {
                    title: '목표설정',
                    description: '효과적인 학습 목표 설정을 지원합니다.',
                    details: ['SMART 목표 설정', '단계별 목표 수립', '달성 전략 계획', '모니터링 체계'],
                    hasChainInteraction: true
                },
                {
                    title: '수학일기',
                    description: '수학 학습 과정을 기록하고 성찰합니다.',
                    details: ['일일 학습 기록', '어려움 점검', '성취 기록', '개선 계획'],
                    hasChainInteraction: true
                },
                {
                    title: '분기목표',
                    description: '장기적 학습 목표를 설정하고 관리합니다.',
                    details: ['분기별 목표 설정', '중간 점검', '진도 조정', '최종 평가'],
                    hasChainInteraction: true
                },
                {
                    title: '시간표',
                    description: '효율적인 학습 시간표를 작성하고 관리합니다.',
                    details: ['시간표 작성', '우선순위 설정', '여유 시간 활용', '일정 조정'],
                    hasChainInteraction: true
                }
            ]
        },
        {
            id: 'exam_preparation',
            title: '시험대비',
            description: '인지관성 유형화를 통한 시험 준비',
            explanation: '개인의 시험 준비 패턴에 맞는 체계적인 대비 전략을 제공합니다.',
            items: [
                {
                    title: '준비상태 진단',
                    description: '현재 시험 준비 상태를 진단하고 평가합니다.',
                    details: ['학습 진도 점검', '이해도 평가', '약점 분석', '시간 계산'],
                    hasChainInteraction: true
                },
                {
                    title: '대비 기간을 구간별로 분할하기',
                    description: '시험까지의 기간을 효과적으로 구간별로 나눕니다.',
                    details: ['기간 분할 전략', '구간별 목표 설정', '진도 계획', '여유 시간 확보'],
                    hasChainInteraction: true
                },
                {
                    title: '구간별 최적화',
                    description: '각 구간에 맞는 최적의 학습 전략을 수립합니다.',
                    details: ['구간별 전략 수립', '집중 영역 선정', '복습 계획', '실전 연습'],
                    hasChainInteraction: true
                },
                {
                    title: '내신테스트, 기출문제 풀이',
                    description: '내신 및 기출문제를 통한 실전 연습을 진행합니다.',
                    details: ['문제 유형 분석', '시간 배분 연습', '오답 분석', '반복 학습'],
                    hasChainInteraction: true
                },
                {
                    title: '최종적 기억인출 기획',
                    description: '시험 직전 최종 기억 인출 전략을 계획합니다.',
                    details: ['핵심 내용 정리', '기억 인출 훈련', '마지막 점검', '컨디션 관리'],
                    hasChainInteraction: true
                }
            ]
        },
        {
            id: 'practical_training',
            title: '실전연습',
            description: '인지관성 유형화를 통한 실전 연습',
            explanation: '실제 시험 상황에서의 최적 성능을 위한 연습을 제공합니다.',
            items: [
                {
                    title: '시간관리 (그냥 ... , 빨리 풀기)',
                    description: '실전에서의 효과적인 시간 관리 전략을 연습합니다.',
                    details: ['시간 배분 전략', '속도 조절 기법', '시간 압박 대응', '효율성 향상'],
                    hasChainInteraction: true
                },
                {
                    title: '실수 조절하기',
                    description: '실전에서 실수를 최소화하는 방법을 연습합니다.',
                    details: ['실수 패턴 분석', '예방 전략', '검토 시스템', '정확도 향상'],
                    hasChainInteraction: true
                },
                {
                    title: '문항풀이 순서 정하기',
                    description: '최적의 문항 풀이 순서를 결정하는 전략을 연습합니다.',
                    details: ['문항 분석 기법', '난이도 판단', '순서 결정 전략', '효율성 극대화'],
                    hasChainInteraction: true
                },
                {
                    title: '초반에 목표점수 수정하기',
                    description: '시험 초반 상황에 따른 목표점수 조정 전략을 연습합니다.',
                    details: ['상황 판단', '목표 조정 기준', '전략 변경', '유연한 대응'],
                    hasChainInteraction: true
                },
                {
                    title: '기회비용 계산하기',
                    description: '문항별 기회비용을 계산하여 최적 선택을 연습합니다.',
                    details: ['비용 분석 기법', '우선순위 결정', '효율성 계산', '전략적 선택'],
                    hasChainInteraction: true
                }
            ]
        },
        {
            id: 'attendance',
            title: '출결관련',
            description: '인지관성 유형화를 통한 출결 관리',
            explanation: '출결 관리를 통한 학습 연속성 확보 및 보강 시스템을 제공합니다.',
            items: [
                {
                    title: '사전보강',
                    description: '예정된 결석에 대한 사전 보강을 계획하고 실행합니다.',
                    details: ['사전 일정 확인', '보강 계획 수립', '자료 준비', '진도 조정'],
                    hasChainInteraction: true
                },
                {
                    title: '전수보강',
                    description: '누적된 결석에 대한 전체적인 보강을 진행합니다.',
                    details: ['누적 결손 분석', '보강 범위 설정', '집중 보강 계획', '성과 측정'],
                    hasChainInteraction: true
                },
                {
                    title: '일정공유 루틴',
                    description: '학습 일정을 체계적으로 공유하고 관리합니다.',
                    details: ['일정 공유 시스템', '알림 설정', '변경 사항 통지', '참여도 관리'],
                    hasChainInteraction: true
                }
            ]
        }
    ]
};

// 인지관성 개선 관련 함수들
const biasModule = {
    // 인지관성 감지
    detectBias: function() {
        console.log('인지관성 감지 시스템 실행');
        // 인지관성 감지 로직
    },

    // 인지관성 교정
    correctBias: function() {
        console.log('인지관성 교정 기능 실행');
        // 인지관성 교정 로직
    },

    // 공정성 평가
    evaluateFairness: function() {
        console.log('공정성 평가 실행');
        // 공정성 평가 로직
    },

    // 인지관성 개선 데이터 반환
    getData: function() {
        return biasData;
    },

    // 다양성 체크
    checkDiversity: function() {
        console.log('다양성 체크 실행');
        // 다양성 체크 로직
    }
};

// 전역으로 노출
window.biasModule = biasModule;