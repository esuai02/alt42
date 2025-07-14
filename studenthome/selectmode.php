<?php
include_once("/home/moodle/public_html/moodle/config.php");
include_once("config.php"); // OpenAI API 설정 포함
global $DB, $USER;
require_login();
$studentid = $_GET["userid"];

// URL 파라미터로 역할 전환 확인
$requestedRole = $_GET['role'] ?? null;

// 데이터베이스에서 사용자 역할 가져오기
$userrole = $DB->get_record_sql("SELECT data FROM mdl_user_info_data where userid='$USER->id' AND fieldid='22'");
$dbRole = $userrole->data;

// URL 파라미터가 있으면 해당 역할 사용, 없으면 DB 역할 사용
if ($requestedRole && in_array($requestedRole, ['teacher', 'student'])) {
    $role = $requestedRole;
} else {
    $role = $dbRole;
}

// 모드 저장 처리
if (isset($_POST['action']) && $_POST['action'] == 'save_modes') {
    header('Content-Type: application/json');
    
    try {
        $teacher_mode = $_POST['teacher_mode'];
        $student_mode = $_POST['student_mode'];
        $teacher_id = $USER->id;
        $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : $studentid;
        
        // 디버그 정보
        error_log("Saving modes - Teacher: $teacher_id, Student: $student_id, T-Mode: $teacher_mode, S-Mode: $student_mode");
        
        // 기존 설정 확인 - Moodle의 get_record 사용
        $existing = $DB->get_record('persona_modes', 
            array('teacher_id' => $teacher_id, 'student_id' => $student_id));
        
        if ($existing) {
            // 업데이트 - Moodle의 update_record 사용
            $update = new stdClass();
            $update->id = $existing->id;
            $update->teacher_mode = $teacher_mode;
            $update->student_mode = $student_mode;
            $update->updated_at = time();
            
            $DB->update_record('persona_modes', $update);
            error_log("Updated existing record ID: " . $existing->id);
        } else {
            // 새로 삽입 - Moodle의 insert_record 사용
            $insert = new stdClass();
            $insert->teacher_id = $teacher_id;
            $insert->student_id = $student_id;
            $insert->teacher_mode = $teacher_mode;
            $insert->student_mode = $student_mode;
            $insert->created_at = time();
            $insert->updated_at = time();
            
            $newid = $DB->insert_record('persona_modes', $insert);
            error_log("Inserted new record ID: " . $newid);
        }
        
        echo json_encode(['success' => true, 'message' => '모드가 성공적으로 저장되었습니다.']);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '데이터베이스 쓰기 오류: ' . $e->getMessage()]);
    }
    exit;
}

// OpenAI API 메시지 변환 처리
if (isset($_POST['action']) && $_POST['action'] == 'transform_message') {
    header('Content-Type: application/json');
    
    try {
        $message = $_POST['message'];
        $teacher_mode = $_POST['teacher_mode'];
        $student_mode = $_POST['student_mode'];
        
        // OpenAI API 호출
        $transformed_message = transformMessageWithOpenAI($message, $teacher_mode, $student_mode);
        
        echo json_encode(['success' => true, 'transformed_message' => $transformed_message]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
    }
    exit;
}

// OpenAI API 메시지 변환 함수
function transformMessageWithOpenAI($message, $teacher_mode, $student_mode) {
    // config.php에서 API 키와 모델 정보 가져오기
    $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null;
    $model = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4o';
    
    if (!$api_key) {
        error_log("OpenAI API 키가 config.php에 설정되지 않았습니다.");
        return applyBasicTransformation($message, $teacher_mode, $student_mode);
    }
    
    $mode_descriptions = [
        'curriculum' => '체계적이고 계획적인 어조',
        'exam' => '긴장감 있고 동기부여적인 어조',
        'custom' => '친근하고 격려하는 어조',
        'mission' => '게임처럼 도전적이고 즉각적인 어조',
        'reflection' => '사려깊고 질문을 유도하는 어조',
        'selfled' => '자율성을 존중하는 제안형 어조'
    ];
    
    $examples = [
        'exam_to_custom' => [
            'original' => '이번 시험 제대로 공부 안하면 큰일날!',
            'transformed' => '수학시험 일정과 위험을 체크해 볼까요?'
        ],
        'curriculum_to_mission' => [
            'original' => '오늘까지 3단원 전체를 완료해야 합니다.',
            'transformed' => '오늘의 미션: 3단원 클리어! 🎯 단계별로 정복해보자!'
        ]
    ];

    $system_prompt = "당신은 선생님의 메시지를 학생의 학습 스타일에 맞게 변환하는 전문 AI입니다.

선생님 모드: {$teacher_mode} ({$mode_descriptions[$teacher_mode]})
학생 모드: {$student_mode} ({$mode_descriptions[$student_mode]})

변환 예시:
- 원본: \"이번 시험 제대로 공부 안하면 큰일날!\"
- 변환: \"수학시험 일정과 위험을 체크해 볼까요?\"

변환 원칙:
1. 핵심 메시지와 의도는 완전히 유지
2. 학생 모드에 맞는 어조와 표현으로 변경
3. 구체적이고 실용적인 표현 사용
4. 한국어로 자연스럽게 표현
5. 변환된 메시지만 출력 (설명 없이)

원본 메시지를 학생에게 맞게 변환해주세요:";
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => "원본 메시지: \"{$message}\""]
        ],
        'temperature' => 0.7,
        'max_tokens' => 200
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return trim($result['choices'][0]['message']['content']);
    } else {
        // API 호출 실패 시 기본 변환 규칙 적용
        error_log("OpenAI API 호출 실패: " . json_encode($result));
        return applyBasicTransformation($message, $teacher_mode, $student_mode);
    }
}

// 기본 변환 규칙 (API 실패 시 대체)
function applyBasicTransformation($message, $teacher_mode, $student_mode) {
    $transformations = [
        'exam' => [
            'custom' => function($msg) { 
                return str_replace(
                    ['큰일날', '제대로', '안하면'], 
                    ['체크해 볼까요', '함께', '놓치면'], 
                    $msg
                ); 
            },
            'mission' => function($msg) { 
                return "🎯 미션: " . str_replace(['해야', '완료'], ['도전', '클리어'], $msg); 
            }
        ],
        'curriculum' => [
            'mission' => function($msg) { 
                return "📚 " . str_replace(['완료해야', '공부'], ['클리어하자', '정복'], $msg); 
            }
        ]
    ];
    
    if (isset($transformations[$teacher_mode][$student_mode])) {
        return $transformations[$teacher_mode][$student_mode]($message);
    }
    
    return $message;
}

// 기존 모드 설정 가져오기
$existing_modes = null;
if ($role == 'teacher' && $studentid) {
    try {
        // Moodle의 get_record 사용 (테이블명에서 mdl_ 제거)
        $existing_modes = $DB->get_record('persona_modes', 
            array('teacher_id' => $USER->id, 'student_id' => $studentid));
    } catch (Exception $e) {
        error_log("Error getting existing modes: " . $e->getMessage());
        $existing_modes = null;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>나의 학습 세계관 선택 시스템</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            color: white;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .screen {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .screen.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateX(50px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        
        @keyframes slideInRight {
            from { 
                opacity: 0; 
                transform: translateX(100px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        
        #studentModeSelection {
            animation: slideIn 0.5s ease-out;
        }
        
        #teacherModeGrid {
            transition: all 0.3s ease-out;
        }

        /* 홈 화면 스타일 */
        .home-title {
            text-align: center;
            font-size: clamp(28px, 5vw, 48px);
            margin-bottom: 20px;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .home-subtitle {
            text-align: center;
            font-size: 20px;
            color: #9ca3af;
            margin-bottom: 40px;
        }

        .modes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 40px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 20px;
        }

        .mode-card {
            border-radius: 20px;
            padding: 40px 30px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
        
        .detail-button {
            position: absolute;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
        }
        
        .detail-button:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .mode-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
        }

        .mode-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .mode-card:hover::before {
            opacity: 1;
        }

        .mode-card.curriculum { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .mode-card.custom { background: linear-gradient(135deg, #10b981, #059669); }
        .mode-card.exam { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .mode-card.mission { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .mode-card.reflection { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .mode-card.selfled { background: linear-gradient(135deg, #6366f1, #4f46e5); }

        .mode-icon {
            font-size: 72px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .mode-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
            line-height: 1.2;
            background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(255,255,255,0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .mode-target {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .mode-hint {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 8px;
            text-align: center;
        }

        .ai-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            border: none;
            border-radius: 50px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin: 0 auto;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(168, 85, 247, 0.4);
        }

        .ai-button:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 30px rgba(168, 85, 247, 0.6);
        }

        /* 설문 화면 스타일 */
        .survey-header {
            margin-bottom: 30px;
        }

        .survey-title {
            font-size: 32px;
            margin-bottom: 20px;
        }

        .progress-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #374151;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #a855f7, #ec4899);
            transition: width 0.3s ease;
        }

        .question-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 20px;
        }

        .question {
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .option-button {
            width: 100%;
            text-align: left;
            padding: 20px;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.1);
            border: 2px solid transparent;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .option-button:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.3);
            transform: translateX(10px);
        }

        /* 결과 화면 스타일 */
        .result-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s ease-out;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .result-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .result-title {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .result-mode {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .result-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            animation: slideUp 1s ease-out;
            animation-fill-mode: both;
        }

        .result-box:nth-child(1) { animation-delay: 0.1s; }
        .result-box:nth-child(2) { animation-delay: 0.2s; }
        .result-box:nth-child(3) { animation-delay: 0.3s; }
        .result-box:nth-child(4) { animation-delay: 0.4s; }
        .result-box:nth-child(5) { animation-delay: 0.5s; }
        .result-box:nth-child(6) { animation-delay: 0.6s; }
        .result-box:nth-child(7) { animation-delay: 0.7s; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .result-box.warning {
            background: rgba(251, 191, 36, 0.1);
            border-color: rgba(251, 191, 36, 0.3);
        }

        .result-box.success {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
        }

        .result-box h3 {
            font-size: 24px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .result-box p, .result-box li {
            font-size: 16px;
            line-height: 1.6;
        }

        .execution-list {
            list-style: none;
            padding-left: 0;
        }

        .execution-list li {
            margin-bottom: 12px;
            padding-left: 30px;
            position: relative;
        }

        .execution-list li::before {
            content: counter(item) ".";
            counter-increment: item;
            position: absolute;
            left: 0;
            font-weight: bold;
            color: #22c55e;
            font-size: 18px;
        }

        .execution-list {
            counter-reset: item;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        #actionButtonsBox {
            display: none;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-button {
            padding: 14px 28px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-button.primary {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .action-button.secondary {
            background: #4b5563;
            color: white;
        }

        .action-button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #9ca3af;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: color 0.2s;
        }

        .back-button:hover {
            color: white;
        }

        /* 전환 버튼 스타일 */
        .switch-button {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 25px;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .switch-button:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
            transform: scale(1.05);
        }
        
        .switch-button::before {
            content: '🔄';
            font-size: 16px;
        }

        /* AI 변환 안내 문구 */
        .ai-conversion-notice {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 16px;
            background: rgba(251, 191, 36, 0.15);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            color: #fbbf24;
            font-size: 12px;
            font-weight: 500;
            backdrop-filter: blur(10px);
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { 
                opacity: 0.8;
                box-shadow: 0 0 10px rgba(251, 191, 36, 0.2);
            }
            50% { 
                opacity: 1;
                box-shadow: 0 0 20px rgba(251, 191, 36, 0.4);
            }
        }

        .teacher-interface .ai-conversion-notice {
            background: rgba(251, 191, 36, 0.15);
            border-color: rgba(251, 191, 36, 0.3);
            color: #d97706;
        }

        /* 선생님용 스타일 - 배경만 변경, 카드는 동일하게 유지 */
        .teacher-interface {
            background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
        }

        .teacher-interface .container {
            background: none;
        }
        
        /* 모든 그리드 통일 - 기본 스타일 사용 */

        /* 모든 인터페이스에서 동일한 카드 스타일 사용 */
        
        /* mode-desc 클래스 제거 - mode-target으로 통일 */

        .teacher-interface .ai-button {
            background: linear-gradient(90deg, #4f46e5 0%, #6366f1 100%);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .teacher-interface .ai-button:hover {
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .teacher-interface .switch-button {
            background: rgba(0,0,0,0.1);
            border: 2px solid rgba(0,0,0,0.2);
            color: #374151;
        }

        .teacher-interface .switch-button:hover {
            background: rgba(0,0,0,0.15);
            border-color: rgba(0,0,0,0.3);
        }

        /* 세트별 스타일링 강화 */
        .mode-card.curriculum, .mode-card.exam {
            border: 2px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.2);
        }
        
        .mode-card.custom, .mode-card.mission {
            border: 2px solid rgba(16, 185, 129, 0.3);
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.2);
        }
        
        .mode-card.reflection, .mode-card.selfled {
            border: 2px solid rgba(139, 92, 246, 0.3);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.2);
        }

        @media (max-width: 1024px) {
            .modes-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 25px;
            }
        }

        @media (max-width: 768px) {
            .modes-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 20px;
                padding: 15px;
            }
            
            .mode-card {
                padding: 30px 20px;
            }
            
            .mode-icon {
                font-size: 56px;
            }
            
            .mode-title {
                font-size: 26px;
            }
            
            .mode-target {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .modes-grid {
                grid-template-columns: 1fr !important;
                gap: 20px;
            }
            
            .mode-card {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- AI 변환 안내 문구 -->
        <div class="ai-conversion-notice" id="aiNotice">
            ✨ 선생님의 대화와 상호작용이 학생의 스타일에 맞게 변환되어 전달됩니다
        </div>
        
        <!-- 역할 표시 및 전환 -->
        <div class="switch-button" onclick="switchRole()" title="클릭하여 모드 전환">
            <span><?php echo $role == 'teacher' ? '선생님 모드' : '학생 모드'; ?></span>
            <span style="font-size: 12px; opacity: 0.8;">→ <?php echo $role == 'teacher' ? '학생' : '선생님'; ?></span>
        </div>
        
        <!-- 매칭 화면 --> 
        <div id="homeScreen" class="screen active">
            <!-- 선생님용 모드 선택 섹션 -->
            <div id="teacherModeSelection" style="display: <?php echo $role == 'teacher' ? 'block' : 'none'; ?>; margin-bottom: 30px;">
                <!-- 제목 -->
                <h1 style="text-align: center; font-size: 36px; margin-bottom: 20px; background: linear-gradient(to right, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    AI 페르소나 매칭 시스템
                </h1>
                <p style="text-align: center; font-size: 20px; color: #9ca3af; margin-bottom: 40px;">
                    선생님과 학생의 스타일을 매칭하여 최적의 학습 경험을 만들어보세요
                </p>
                
                <!-- 안내 메시지 -->
                <div class="result-box" id="teacherGuideBox" style="background: rgba(251, 191, 36, 0.1); border-color: rgba(251, 191, 36, 0.3); margin-bottom: 30px;">
                    <h3><span>📢</span> 사용법</h3>
                    <p>1. 선생님의 교육 철학을 선택합니다<br>
                       2. 학생의 학습 스타일을 선택합니다<br>
                       3. "채팅 시작하기" 버튼을 클릭하면 메시지가 자동 변환됩니다<br>
                       <strong>예시: "이번 시험 제대로 공부 안하면 큰일날!" → "수학시험 일정과 위험을 체크해 볼까요?"</strong></p>
                </div>
                
                <!-- 선생님 모드 선택 박스 -->
                <div class="result-box" id="teacherModeBox" style="background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3);">
                    <h3><span>👨‍🏫</span> 선생님의 교육 철학을 선택해주세요</h3>
                    <p>선택한 교육 철학에 따라 학생과의 대화가 자동으로 변환됩니다.</p>
                    <div id="teacherModeGrid" class="modes-grid" style="margin-top: 20px;">
                            <?php
                            $teacher_modes = [
                                'curriculum' => ['icon' => '📚', 'title' => '체계적 진도형', 'desc' => '교과 과정을 체계적으로 따라가며 기초부터 심화까지 단계별로 지도하는 교육 철학'],
                                'exam' => ['icon' => '✏️', 'title' => '성과 집중형', 'desc' => '시험과 평가에 최적화된 전략적 지도로 단기간 성적 향상을 추구하는 교육 철학'],
                                'custom' => ['icon' => '🎯', 'title' => '개인 맞춤형', 'desc' => '학생 개개인의 수준과 특성을 파악하여 맞춤형 학습 경로를 제시하는 교육 철학'],
                                'mission' => ['icon' => '⚡', 'title' => '목표 달성형', 'desc' => '작은 성취를 쌓아가며 동기부여를 극대화하는 단기 미션 중심의 교육 철학'],
                                'reflection' => ['icon' => '🧠', 'title' => '사고력 중심형', 'desc' => '정답보다 과정을 중시하며 학생의 사고력과 문제해결력을 기르는 교육 철학'],
                                'selfled' => ['icon' => '🚀', 'title' => '자율 학습형', 'desc' => '학생의 자기주도성을 존중하고 스스로 학습을 설계하도록 돕는 교육 철학']
                            ];
                            foreach ($teacher_modes as $key => $mode): ?>
                                <div class="mode-card <?php echo $key; ?>" onclick="selectTeacherMode('<?php echo $key; ?>')">
                                    <div class="mode-icon"><?php echo $mode['icon']; ?></div>
                                    <div class="mode-title"><?php echo str_replace(' ', '<br>', $mode['title']); ?></div>
                                    <div class="mode-target"><?php echo $mode['desc']; ?></div>
                                    <button class="detail-button" onclick="event.stopPropagation(); showTeacherModeDetail('<?php echo $key; ?>')">자세히</button>
                                </div>
                            <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- 학생 모드 선택 박스 (선생님 모드 선택 후 표시) -->
                <div class="result-box" id="studentModeBox" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); display: none;">
                    <h3><span>👨‍🎓</span> 학생의 학습 스타일을 선택해주세요</h3>
                    <p>학생의 성향에 맞는 학습 모드를 선택하면 대화가 자동으로 변환됩니다.</p>
                    <div id="studentModeGrid" class="modes-grid" style="margin-top: 20px;">
                        <!-- JavaScript로 동적 생성됨 -->
                    </div>
                </div>
                
                <!-- 채팅 시작 버튼 (두 모드 모두 선택 후 표시) -->
                <div class="action-buttons" id="actionButtonsBox" style="display: none; margin-top: 30px;">
                    <button class="action-button primary" onclick="startChat()" id="startChatBtn">
                        <span>💬</span>
                        <span>채팅 시작하기</span>
                    </button>
                    <button class="action-button secondary" onclick="resetSelection()">
                        <span>↻</span>
                        <span>다시 선택하기</span>
                    </button>
                </div>
                
                <!-- 테스트 버튼 (개발용) -->
                <div style="margin-top: 20px; text-align: center;">
                    <button class="action-button secondary" onclick="testModeSelection()" style="display: inline-flex;">
                        <span>🔧</span>
                        <span>테스트</span>
                    </button>
                </div>
            </div>
            
            <!-- 학생 모드 선택 섹션 -->
            <div id="studentModeSelection" style="display: <?php echo $role == 'student' ? 'block' : 'none'; ?>;">
                <div class="result-box" style="background: rgba(251, 191, 36, 0.1); border-color: rgba(251, 191, 36, 0.3); margin-bottom: 20px;">
                    <h3><span>📚</span> 나에게 맞는 학습 모드를 선택하세요</h3>
                    <p>당신의 학습 스타일에 맞는 모드를 선택하면 최적화된 학습 경험을 제공합니다.</p>
                </div>
                
                <div class="modes-grid" id="studentModesGrid">
                    <!-- 첫 번째 행: 커리큘럼, 시험대비 -->
                    <div class="mode-card curriculum" onclick="selectStudentModeForStudent('curriculum')">
                        <div class="mode-icon">📚</div>
                        <div class="mode-title">커리큘럼<br>중심모드</div>
                        <div class="mode-target">상위권, 목표 대학 있는 유형</div>
                        <button class="detail-button" onclick="event.stopPropagation(); showStudentModeDetail('curriculum')">자세히</button>
                    </div>
                
                    <div class="mode-card exam" onclick="selectStudentModeForStudent('exam')">
                        <div class="mode-icon">✏️</div>
                        <div class="mode-title">시험대비<br>중심모드</div>
                        <div class="mode-target">시험에 죽고 사는 유형, 동기부여 자가발전 타입</div>
                        <button class="detail-button" onclick="event.stopPropagation(); showStudentModeDetail('exam')">자세히</button>
                    </div>
                    
                    <!-- 두 번째 행: 맞춤학습, 단기미션 -->
                    <div class="mode-card custom" onclick="selectStudentModeForStudent('custom')">
                        <div class="mode-icon">🎯</div>
                        <div class="mode-title">맞춤학습<br>중심모드</div>
                        <div class="mode-target">기초 부족, 스스로 학습이 익숙하지 않은 학생</div>
                        <button class="detail-button" onclick="event.stopPropagation(); showStudentModeDetail('custom')">자세히</button>
                    </div>
                    
                    <div class="mode-card mission" onclick="selectStudentModeForStudent('mission')">
                        <div class="mode-icon">⚡</div>
                        <div class="mode-title">단기미션<br>중심모드</div>
                        <div class="mode-target">집중력 낮고 루틴이 없는 학생</div>
                        <button class="detail-button" onclick="event.stopPropagation(); showStudentModeDetail('mission')">자세히</button>
                    </div>
                    
                    <!-- 세 번째 행: 자기성찰, 자기주도 -->
                    <div class="mode-card reflection" onclick="selectStudentModeForStudent('reflection')">
                        <div class="mode-icon">🧠</div>
                        <div class="mode-title">자기성찰<br>중심모드</div>
                        <div class="mode-target">고민은 많고 생각은 깊은데 실행은 없는 학생</div>
                        <button class="detail-button" onclick="event.stopPropagation(); showStudentModeDetail('reflection')">자세히</button>
                    </div>
                    
                    <div class="mode-card selfled" onclick="selectStudentModeForStudent('selfled')">
                        <div class="mode-icon">🚀</div>
                        <div class="mode-title">자기주도 시나리오<br>중심모드</div>
                        <div class="mode-target">자율성 높은 중·상위권, "나만의 공부법" 선호자</div>
                        <button class="detail-button" onclick="event.stopPropagation(); showStudentModeDetail('selfled')">자세히</button>
                    </div>
                </div>
                
                <button class="ai-button" onclick="startSurvey()">
                    <span>✨</span>
                    <span>AI 추천 받기 (유형 검사)</span>
                    <span>→</span>
                </button>
            </div>
        </div>

        <!-- 설문 화면 -->
        <div id="surveyScreen" class="screen">
            <div class="survey-header">
                <h2 class="survey-title">학습 유형 검사</h2>
                <div class="progress-container">
                    <span id="progressText">1 / 4</span>
                </div>
                <div class="progress-bar">
                    <div id="progressFill" class="progress-fill" style="width: 25%"></div>
                </div>
            </div>
            
            <div class="question-box">
                <h3 id="questionText" class="question"></h3>
                <div id="optionsContainer"></div>
            </div>
            
            <button class="back-button" onclick="goHome()">
                <span>←</span>
                <span>처음으로 돌아가기</span>
            </button>
        </div>

        <!-- 결과 화면 -->
        <div id="resultScreen" class="screen">
            <div id="resultContent"></div>
        </div>
        
        <!-- 메시지 변환 데모 화면 -->
        <div id="messageTransformScreen" class="screen">
            <div class="survey-header">
                <h2 class="survey-title">AI 메시지 변환 데모</h2>
                <p style="color: #9ca3af; text-align: center;">선생님의 메시지가 학생 스타일에 맞게 어떻게 변환되는지 확인해보세요</p>
            </div>
            
            <div class="result-box">
                <h3><span>📝</span> 선생님 메시지 입력</h3>
                <textarea id="teacherMessageInput" placeholder="예: 이번 시험 제대로 공부 안하면 큰일날!" 
                    style="width: 100%; min-height: 100px; padding: 15px; border-radius: 8px; border: 1px solid #374151; background: rgba(255,255,255,0.1); color: white; resize: vertical;"></textarea>
            </div>
            
            <div class="result-box">
                <h3><span>⚡</span> 현재 설정</h3>
                <p>선생님 모드: <strong id="currentTeacherMode">선택되지 않음</strong></p>
                <p>학생 모드: <strong id="currentStudentMode">선택되지 않음</strong></p>
            </div>
            
            <div class="action-buttons">
                <button class="action-button primary" onclick="transformMessage()" id="transformBtn" disabled>
                    <span>🤖</span>
                    <span>메시지 변환하기</span>
                </button>
            </div>
            
            <div id="transformedResult" class="result-box success" style="display: none;">
                <h3><span>✨</span> 변환된 메시지</h3>
                <div id="transformedMessageContent" style="padding: 15px; background: rgba(34, 197, 94, 0.1); border-radius: 8px; border-left: 4px solid #22c55e;"></div>
            </div>
            
            <button class="back-button" onclick="goHome()">
                <span>←</span>
                <span>처음으로 돌아가기</span>
            </button>
        </div>
    </div>

    <script>
        // 현재 인터페이스 상태
        let isTeacherMode = <?php echo $role == 'teacher' ? 'true' : 'false'; ?>;
        let selectedTeacherMode = null;
        let selectedStudentMode = null;

        // 학습 모드 데이터 (학생용)
        const studentModes = {
            curriculum: {
                title: '커리큘럼 중심모드',
                icon: '📚',
                target: '상위권, 목표 대학 있는 유형',
                description: '고강도 선행과 개념 완성 루트 설계',
                mathking: '학습 로드맵 자동생성, 진도율 분석',
                management: '진도이탈 탐지 → 일정 리마인드, 선행과 복습 균형 관리',
                heavyMessage: '너는 이제 대학이라는 목표를 향해 달리는 마라토너다. 중간에 멈추면 그 자리가 네 무덤이 된다.',
                executionPoints: [
                    '매일 정해진 시간에 학습 시작 - 예외는 없다',
                    '주간 진도 체크를 통한 자기 검증 필수',
                    '선행과 복습의 황금비율 7:3 유지',
                    '월 1회 전체 커리큘럼 점검 및 수정',
                    '번아웃 징조 발견 시 즉시 페이스 조절'
                ]
            },
            custom: {
                title: '맞춤학습 중심모드',
                icon: '🎯',
                target: '기초 부족, 스스로 학습이 익숙하지 않은 학생',
                description: '개별 수준 맞춤 문제 배치와 진단 루프 활용',
                mathking: '진단평가 → 맞춤 콘텐츠 자동 제공',
                management: '학습 이탈 경보 활용, 히스토리 기반 개입 시점 자동화',
                heavyMessage: '기초가 없는 건물은 무너진다. 너의 부족함을 인정하는 것부터가 시작이다. 부끄러움은 사치다.',
                executionPoints: [
                    '진단 결과를 있는 그대로 받아들이기',
                    '하루 최소 2시간 기초 개념 반복 학습',
                    '모르는 것을 적는 "무지 노트" 작성',
                    '주 3회 이상 AI 튜터와 1:1 세션',
                    '작은 성취도 기록하며 자신감 쌓기'
                ]
            },
            exam: {
                title: '시험대비 중심모드',
                icon: '✏️',
                target: '시험에 죽고 사는 유형, 동기부여 자가발전 타입',
                description: '내신 분석 → 파이널 기억인출 구조 세팅',
                mathking: '단원별 출제 빈도 분석, Final리뷰 구성',
                management: '시험 3~4주 전 계획 리마인드, 예상문제 정확도 추적',
                heavyMessage: '시험은 전쟁이고, 성적은 네 무기다. 1점에 울고 웃는 게 현실이면, 그 1점에 목숨을 걸어라.',
                executionPoints: [
                    'D-30부터 시작하는 철저한 시험 대비',
                    '매일 밤 그날 배운 내용 백지 복습',
                    '기출문제는 3회독 - 틀릴 때까지',
                    '시험 당일 컨디션 관리 루틴 확립',
                    '시험 후 오답 분석은 48시간 내 완료'
                ]
            },
            mission: {
                title: '단기미션 중심모드',
                icon: '⚡',
                target: '집중력 낮고 루틴이 없는 학생',
                description: '짧은 목표 → 성취 → 피드백 → 반복 학습 루프',
                mathking: '미션 과제 단위로 제공 + 피드백 자동 누적',
                management: '미션 완료율 체크, 짧은 주기 성취 기록 강조',
                heavyMessage: '넌 지금 게임 중독자처럼 공부에 중독되어야 한다. 도파민을 학습으로 채워라. 그게 네 구원이다.',
                executionPoints: [
                    '하루 5개 미션 - 실패 시 다음날 7개',
                    '미션 클리어 스트릭 최소 7일 유지',
                    '10분 집중, 5분 휴식 포모도로 기법',
                    '달성률 80% 미만 시 난이도 재조정',
                    '주간 보상 시스템으로 동기 유지'
                ]
            },
            reflection: {
                title: '자기성찰 중심모드',
                icon: '🧠',
                target: '고민은 많고 생각은 깊은데 실행은 없는 학생',
                description: '학습 후 자기평가 → 피드백 기록 → 학습전략 수정',
                mathking: '학습일지 작성 기능, 자기 피드백 작성',
                management: '일지 작성 여부 주기적 확인, 내용 키워드 분석',
                heavyMessage: '생각만 하는 자는 아무것도 이루지 못한다. 네 머릿속 계획이 현실이 되지 않으면 그건 망상일 뿐이다.',
                executionPoints: [
                    '매일 밤 10분 학습 일지 작성 의무화',
                    '주간 메타인지 체크리스트 작성',
                    '실행하지 않은 계획은 "실패 기록"에',
                    '월 1회 학습 전략 전면 재검토',
                    '생각과 행동의 갭 측정 및 개선'
                ]
            },
            selfled: {
                title: '자기주도 시나리오 중심모드',
                icon: '🚀',
                target: '자율성 높은 중·상위권, "나만의 공부법" 선호자',
                description: '수업 시나리오를 본인이 직접 설계하고 주도',
                mathking: '수업 플랜 템플릿 제공 + 커스텀 루트 설계',
                management: '시나리오 목표와 실제 실행 비교, 피드백 순환 설계',
                heavyMessage: '네가 직접 설계한 수업이 망하면, 그건 선생님 탓이 아니라 네가 만든 실패야. 주인공이면 책임도 지는 거야.',
                executionPoints: [
                    '주간 학습 계획 직접 수립 및 공유',
                    '실패한 계획은 원인 분석 후 수정',
                    '자기 주도 학습 시간 최소 70% 확보',
                    '멘토/동료와 월 2회 피드백 세션',
                    '분기별 학습 포트폴리오 제작'
                ]
            }
        };

        // 선생님용 모드 데이터
        const teacherModes = {
            curriculum: {
                title: '체계적 진도형',
                icon: '📚',
                desc: '교과 과정을 체계적으로 따라가며 기초부터 심화까지 단계별로 지도하는 교육 철학',
                description: '도전적인 커리큘럼을 제시하여 시중 교재 대비 필요한 부분을 집중적으로 강화',
                focus: '• 학습 한계 상황을 적시에 해소<br />• 매주 진단 후 계획을 재조정'
            },
            exam: {
                title: '성과 집중형',
                icon: '✏️',
                desc: '시험과 평가에 최적화된 전략적 지도로 단기간 성적 향상을 추구하는 교육 철학',
                description: '시험 전 1주일 동안 모든 과정을 정렬하고 실전 테스트 레벨업 + 보충학습 구조 운영',
                focus: '• 매일 시험 전날과 같은 텐션 유지<br />• 실전 감각 강화를 위한 피드백'
            },
            custom: {
                title: '개인 맞춤형',
                icon: '🎯',
                desc: '학생 개개인의 수준과 특성을 파악하여 맞춤형 학습 경로를 제시하는 교육 철학',
                description: '촘촘한 NEXT STEP을 설계하고 조정 가능한 방식으로 학습을 진행',
                focus: '• 학생 고유 UX 축적<br />• 시험대비 로드맵 모순점 제거'
            },
            mission: {
                title: '목표 달성형',
                icon: '⚡',
                desc: '작은 성취를 쌓아가며 동기부여를 극대화하는 단기 미션 중심의 교육 철학',
                description: '작심 30분 시스템으로 단기 목표 달성에 집중',
                focus: '• 맞춤형 포모도르 설정 및 자기 조절력 강화<br />• 명확한 결과 피드백 제공'
            },
            reflection: {
                title: '사고력 중심형',
                icon: '🧠',
                desc: '정답보다 과정을 중시하며 학생의 사고력과 문제해결력을 기르는 교육 철학',
                description: '학생 스스로 질문‧응답을 촉진하여 자기 이해를 심화',
                focus: '• 시험 및 주간 목표 중심 주제 가이드<br />• 시험대비 모드 스위칭 구간 설정'
            },
            selfled: {
                title: '자율 학습형',
                icon: '🚀',
                desc: '학생의 자기주도성을 존중하고 스스로 학습을 설계하도록 돕는 교육 철학',
                description: '학생이 커리큘럼 수립부터 오답노트까지 전 과정을 주도',
                focus: '• 선택의 장단점 성찰 지원<br />• 분기 단위 딜레마 요소 최적화'
            }
        };

        // 설문 질문
        const surveyQuestions = [
            {
                question: "현재 나의 학업 수준은?",
                options: [
                    { text: "상위권 (1~2등급)", value: "high" },
                    { text: "중위권 (3~5등급)", value: "mid" },
                    { text: "하위권 (6등급 이하)", value: "low" }
                ]
            },
            {
                question: "공부할 때 가장 큰 고민은?",
                options: [
                    { text: "무엇을 공부해야 할지 모르겠어요", value: "direction" },
                    { text: "집중력이 부족해요", value: "focus" },
                    { text: "시험 성적이 오르지 않아요", value: "exam" },
                    { text: "공부법이 맞는지 확신이 없어요", value: "method" }
                ]
            },
            {
                question: "선호하는 학습 스타일은?",
                options: [
                    { text: "체계적인 계획을 따르는 것", value: "systematic" },
                    { text: "짧은 목표를 달성하며 나아가는 것", value: "short-term" },
                    { text: "스스로 계획을 세우고 실행하는 것", value: "self-directed" },
                    { text: "꾸준히 기록하고 돌아보는 것", value: "reflective" }
                ]
            },
            {
                question: "학습 목표는?",
                options: [
                    { text: "명문대 입학", value: "top-university" },
                    { text: "내신 성적 향상", value: "grades" },
                    { text: "기초 실력 다지기", value: "foundation" },
                    { text: "자기주도 학습 능력 개발", value: "self-learning" }
                ]
            }
        ];

        let currentQuestion = 0;
        let surveyAnswers = {};

        // 화면 전환 함수
        function showScreen(screenId) {
            document.querySelectorAll('.screen').forEach(screen => {
                screen.classList.remove('active');
            });
            document.getElementById(screenId).classList.add('active');
        }

        // 홈으로 돌아가기
        function goHome() {
            currentQuestion = 0;
            surveyAnswers = {};
            showScreen('homeScreen');
        }

        // 설문 시작
        function startSurvey() {
            showScreen('surveyScreen');
            showQuestion();
        }

        // 질문 표시
        function showQuestion() {
            const question = surveyQuestions[currentQuestion];
            document.getElementById('questionText').textContent = question.question;
            
            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';
            
            question.options.forEach(option => {
                const button = document.createElement('button');
                button.className = 'option-button';
                button.textContent = option.text;
                button.onclick = () => selectAnswer(option.value);
                optionsContainer.appendChild(button);
            });
            
            // 진행률 업데이트
            const progress = ((currentQuestion + 1) / surveyQuestions.length) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('progressText').textContent = `${currentQuestion + 1} / ${surveyQuestions.length}`;
        }

        // 답변 선택
        function selectAnswer(value) {
            surveyAnswers[currentQuestion] = value;
            
            if (currentQuestion < surveyQuestions.length - 1) {
                currentQuestion++;
                showQuestion();
            } else {
                const recommendedMode = analyzeAnswers(surveyAnswers);
                showResult(recommendedMode, "AI가 당신의 답변을 분석한 결과입니다.");
            }
        }

        // 역할 전환 함수
        function switchRole() {
            // 현재 역할을 전환
            const currentRole = isTeacherMode ? 'teacher' : 'student';
            const newRole = currentRole === 'teacher' ? 'student' : 'teacher';
            
            // URL에 역할 파라미터 추가하여 페이지 새로고침
            const url = new URL(window.location.href);
            url.searchParams.set('role', newRole);
            
            // 확인 메시지
            if (confirm(`${newRole === 'teacher' ? '선생님' : '학생'} 모드로 전환하시겠습니까?`)) {
                window.location.href = url.toString();
            }
        }

        // 모드 카드 업데이트
        // updateModeCards 함수는 사용하지 않음 - PHP로 이미 렌더링됨
        function updateModeCards() {
            console.warn('updateModeCards() called but should not be used - cards are already rendered by PHP');
            return; // 함수 실행 중단
        }

        // 답변 분석
        function analyzeAnswers(answers) {
            const modeScores = {
                curriculum: 0,
                custom: 0,
                exam: 0,
                mission: 0,
                reflection: 0,
                selfled: 0
            };

            const level = answers[0];
            const concern = answers[1];
            const style = answers[2];
            const goal = answers[3];

            // 학업 수준 기반 점수
            if (level === 'high') {
                if (concern === 'method') {
                    modeScores.reflection += 3;
                    modeScores.curriculum += 1;
                } else if (goal === 'top-university') {
                    modeScores.curriculum += 3;
                } else {
                    modeScores.selfled += 2;
                }
            } else if (level === 'mid') {
                if (concern === 'exam' && goal === 'grades') {
                    modeScores.exam += 3;
                } else if (concern === 'focus') {
                    modeScores.mission += 3;
                } else {
                    modeScores.custom += 2;
                }
            } else {
                if (concern === 'direction') {
                    modeScores.custom += 4;
                } else if (style === 'short-term') {
                    modeScores.mission += 3;
                    modeScores.custom += 1;
                } else {
                    modeScores.custom += 3;
                }
            }

            // 추가 점수 로직
            switch (concern) {
                case 'direction': modeScores.custom += 2; break;
                case 'focus': modeScores.mission += 2; break;
                case 'exam': modeScores.exam += 2; break;
                case 'method': modeScores.reflection += 2; break;
            }

            switch (style) {
                case 'systematic': 
                    if (level === 'high') modeScores.curriculum += 2;
                    else modeScores.custom += 2;
                    break;
                case 'short-term': modeScores.mission += 2; break;
                case 'self-directed': 
                    if (level === 'low') {
                        modeScores.mission += 1;
                        modeScores.custom += 1;
                    } else {
                        modeScores.selfled += 2;
                    }
                    break;
                case 'reflective': modeScores.reflection += 2; break;
            }

            // 최고 점수 모드 찾기
            let maxScore = 0;
            let selectedMode = 'custom';
            
            for (const [mode, score] of Object.entries(modeScores)) {
                if (score > maxScore) {
                    maxScore = score;
                    selectedMode = mode;
                }
            }

            return selectedMode;
        }

        // 직접 선택 (삭제됨 - selectStudentModeForStudent로 대체)
        function selectMode(mode) {
            // 이 함수는 더 이상 사용되지 않습니다
            console.warn('selectMode is deprecated. Use selectStudentModeForStudent instead.');
        }
        
        // 학생이 학생 모드를 선택할 때 (학생 인터페이스)
        function selectStudentModeForStudent(mode) {
            // 선택만 하고 결과 화면으로 가지 않음
            selectedStudentMode = mode;
            
            // 선택된 카드 스타일 업데이트
            document.querySelectorAll('#studentModesGrid .mode-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`#studentModesGrid .mode-card.${mode}`).classList.add('selected');
            
            // 팝업 없이 선택만 처리
        }
        
        // 선생님 모드 상세 보기
        function showTeacherModeDetail(mode) {
            showResult(mode, "선택한 교육 철학의 상세 정보입니다.", true);
        }
        
        // 학생 모드 상세 보기
        function showStudentModeDetail(mode) {
            showResult(mode, "선택한 학습 모드의 상세 정보입니다.", false);
        }

        // 결과 표시
        function showResult(modeKey, analysisReason, isTeacherModeDetail = null) {
            // isTeacherModeDetail이 명시적으로 전달되지 않으면 전역 isTeacherMode 사용
            const showTeacherMode = isTeacherModeDetail !== null ? isTeacherModeDetail : isTeacherMode;
            const mode = showTeacherMode ? teacherModes[modeKey] : studentModes[modeKey];
            showScreen('resultScreen');
            
            let resultHTML = '';
            
            if (showTeacherMode) {
                resultHTML = `
                    <div class="result-header">
                        <div class="result-icon">${mode.icon}</div>
                        <h1 class="result-title">선택한 교육 철학</h1>
                        <div class="result-mode" style="background: linear-gradient(to right, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            ${mode.title}
                        </div>
                    </div>

                    <div class="result-box">
                        <h3><span>🔍</span> 선택 결과</h3>
                        <p>${analysisReason}</p>
                    </div>

                    <div class="result-box">
                        <h3><span>📚</span> 교육 방식</h3>
                        <p>${mode.description}</p>
                    </div>

                    <div class="result-box">
                        <h3><span>🎯</span> 핵심 포인트</h3>
                        <div>${mode.focus}</div>
                    </div>

                    <div class="action-buttons">
                        <button class="action-button primary" onclick="startChat()">
                            <span>💬</span>
                            <span>대화 시작하기</span>
                        </button>
                        <button class="action-button secondary" onclick="goHome()">
                            <span>↻</span>
                            <span>다시 선택하기</span>
                        </button>
                    </div>
                `;
            } else {
                resultHTML = `
                    <div class="result-header">
                        <div class="result-icon">${mode.icon}</div>
                        <h1 class="result-title">당신의 학습 세계관</h1>
                        <div class="result-mode" style="background: linear-gradient(to right, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            ${mode.title}
                        </div>
                    </div>

                    <div class="result-box">
                        <h3><span>🔍</span> AI 분석 결과</h3>
                        <p>${analysisReason}</p>
                    </div>

                    <div class="result-box warning">
                        <h3><span>💡</span> 현실조언</h3>
                        <p style="font-weight: bold;">${mode.heavyMessage}</p>
                    </div>

                    <div class="result-box success">
                        <h3><span>✅</span> 실행 포인트 - 지금 당장 해야 할 것들</h3>
                        <ul class="execution-list">
                            ${mode.executionPoints.map(point => `<li>${point}</li>`).join('')}
                        </ul>
                    </div>

                    <div class="result-box">
                        <h3><span>👥</span> 타겟 학생</h3>
                        <p>${mode.target}</p>
                    </div>

                    <div class="result-box">
                        <h3><span>📚</span> 학습 방식</h3>
                        <p>${mode.description}</p>
                    </div>

                    <div class="result-box">
                        <h3><span>🤖</span> Mathking 활용법</h3>
                        <p>${mode.mathking}</p>
                    </div>

                    <div class="result-box">
                        <h3><span>📊</span> 관리 포인트</h3>
                        <p>${mode.management}</p>
                    </div>

                    <div class="action-buttons">
                        <button class="action-button primary" onclick="startChat()">
                            <span>💬</span>
                            <span>대화 시작하기</span>
                        </button>
                        <button class="action-button secondary" onclick="goHome()">
                            <span>↻</span>
                            <span>다시 선택하기</span>
                        </button>
                    </div>
                `;
            }
            
            document.getElementById('resultContent').innerHTML = resultHTML;
        }

        // 선생님용 모드 선택 그리드 생성
        function createModeSelectionGrids() {
            const teacherGrid = document.getElementById('teacherModeGrid');
            const studentGrid = document.getElementById('studentModeGrid');
            
            console.log('createModeSelectionGrids 호출됨');
            console.log('teacherModes:', Object.keys(teacherModes).length, '개');
            console.log('studentModes:', Object.keys(studentModes).length, '개');
            
            // 선생님 모드 그리드 생성
            teacherGrid.innerHTML = '';
            let teacherCount = 0;
            Object.entries(teacherModes).forEach(([key, mode]) => {
                teacherCount++;
                console.log(`선생님 모드 ${teacherCount}: ${key} - ${mode.title}`);
                const card = document.createElement('div');
                card.className = `mode-card ${key} ${selectedTeacherMode === key ? 'selected' : ''}`;
                card.onclick = () => selectTeacherMode(key);
                card.innerHTML = `
                    <div class="mode-icon">${mode.icon}</div>
                    <div class="mode-title">${mode.title}</div>
                    <div class="mode-desc">${mode.description}</div>
                `;
                teacherGrid.appendChild(card);
            });
            
            // 학생 모드 그리드 생성
            studentGrid.innerHTML = '';
            let studentCount = 0;
            Object.entries(studentModes).forEach(([key, mode]) => {
                studentCount++;
                console.log(`학생 모드 ${studentCount}: ${key} - ${mode.title}`);
                const card = document.createElement('div');
                card.className = `mode-card ${key} ${selectedStudentMode === key ? 'selected' : ''}`;
                card.onclick = () => selectStudentMode(key);
                card.innerHTML = `
                    <div class="mode-icon">${mode.icon}</div>
                    <div class="mode-title">${mode.title}</div>
                    <div class="mode-target">${mode.target}</div>
                `;
                studentGrid.appendChild(card);
            });
            
            console.log(`총 생성된 카드: 선생님 ${teacherCount}개, 학생 ${studentCount}개`);
        }

        // 선생님 모드 선택 - 전역 함수로 등록
        window.selectTeacherMode = function(mode) {
            console.log('선생님 모드 선택됨:', mode);
            selectedTeacherMode = mode;
            
            // 선택된 카드 스타일 업데이트
            document.querySelectorAll('#teacherModeGrid .mode-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`#teacherModeGrid .mode-card.${mode}`).classList.add('selected');
            
            // 학생 모드 선택 박스 표시
            const studentModeBox = document.getElementById('studentModeBox');
            const studentGrid = document.getElementById('studentModeGrid');
            
            console.log('studentModeBox 찾음:', !!studentModeBox);
            console.log('studentGrid 찾음:', !!studentGrid);
            console.log('studentModeBox 현재 display:', studentModeBox ? window.getComputedStyle(studentModeBox).display : 'null');
            
            // 강제로 보이도록 테스트
            if (!studentModeBox) {
                console.error('studentModeBox를 찾을 수 없습니다!');
                return;
            }
            
            // 학생 모드 카드 생성
            const studentModeCards = {
                curriculum: { icon: '📚', title: '커리큘럼<br>중심모드', desc: '상위권, 목표 대학 있는 유형' },
                exam: { icon: '✏️', title: '시험대비<br>중심모드', desc: '시험에 집중하는 유형, 동기부여 타입' },
                custom: { icon: '🎯', title: '맞춤학습<br>중심모드', desc: '기초 부족, 스스로 학습이 익숙하지 않은 학생' },
                mission: { icon: '⚡', title: '단기미션<br>중심모드', desc: '집중력 낮고 루틴이 없는 학생' },
                reflection: { icon: '🧠', title: '자기성찰<br>중심모드', desc: '고민은 많고 생각은 깊은데 실행은 없는 학생' },
                selfled: { icon: '🚀', title: '자기주도<br>중심모드', desc: '자율성 높은 중·상위권 학생' }
            };
            
            studentGrid.innerHTML = Object.entries(studentModeCards).map(([key, mode]) => `
                <div class="mode-card ${key}" onclick="selectStudentMode('${key}')">
                    <div class="mode-icon">${mode.icon}</div>
                    <div class="mode-title">${mode.title}</div>
                    <div class="mode-target">${mode.desc}</div>
                    <button class="detail-button" onclick="event.stopPropagation(); showStudentModeDetail('${key}')">자세히</button>
                </div>
            `).join('');
            
            // 애니메이션과 함께 학생 모드 박스 표시
            console.log('학생 모드 박스 표시 시작');
            
            // 먼저 display를 block으로 설정
            studentModeBox.style.display = 'block';
            studentModeBox.style.visibility = 'visible';
            studentModeBox.style.opacity = '0';
            studentModeBox.style.transform = 'translateY(20px)';
            
            console.log('display 설정 후:', window.getComputedStyle(studentModeBox).display);
            
            // 애니메이션 시작
            requestAnimationFrame(() => {
                studentModeBox.style.opacity = '1';
                studentModeBox.style.transform = 'translateY(0)';
                studentModeBox.style.transition = 'all 0.3s ease-out';
                console.log('애니메이션 적용 완료');
            });
            
            // 스크롤 이동 (학생 모드 박스가 보이도록)
            setTimeout(() => {
                studentModeBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 400);
        }

        // 학생 모드 선택 - 전역 함수로 등록
        window.selectStudentMode = function(mode) {
            console.log('학생 모드 선택됨:', mode);
            selectedStudentMode = mode;
            
            // 선택된 카드 스타일 업데이트
            document.querySelectorAll('#studentModeGrid .mode-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`#studentModeGrid .mode-card.${mode}`).classList.add('selected');
            
            // 액션 버튼 표시
            const actionButtonsBox = document.getElementById('actionButtonsBox');
            actionButtonsBox.style.display = 'flex';
            actionButtonsBox.style.opacity = '0';
            actionButtonsBox.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                actionButtonsBox.style.opacity = '1';
                actionButtonsBox.style.transform = 'translateY(0)';
                actionButtonsBox.style.transition = 'all 0.3s ease-out';
            }, 50);
        }

        // 선택된 선생님 모드 정보 표시
        function updateSelectedTeacherInfo() {
            const selectedMode = teacherModes[selectedTeacherMode];
            const infoDiv = document.getElementById('selectedTeacherInfo');
            if (infoDiv) {
                infoDiv.innerHTML = `
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); 
                                padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: #3b82f6;">선택된 선생님 모드</h4>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 36px;">${selectedMode.icon}</span>
                            <div>
                                <strong style="font-size: 18px;">${selectedMode.title}</strong>
                                <p style="margin: 5px 0 0 0; opacity: 0.8;">${selectedMode.desc}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        // 선택된 선생님 모드 배지 표시
        function showSelectedTeacherBadge() {
            const selectedMode = teacherModes[selectedTeacherMode];
            
            // 기존 배지 제거
            const existingBadge = document.getElementById('teacherBadge');
            if (existingBadge) existingBadge.remove();
            
            // 새 배지 생성
            const badge = document.createElement('div');
            badge.id = 'teacherBadge';
            badge.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: rgba(59, 130, 246, 0.9);
                color: white;
                padding: 15px 20px;
                border-radius: 30px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 1000;
                animation: slideInRight 0.5s ease-out;
            `;
            badge.innerHTML = `
                <span style="font-size: 24px;">${selectedMode.icon}</span>
                <div>
                    <div style="font-size: 12px; opacity: 0.8;">선생님 모드</div>
                    <div style="font-weight: bold;">${selectedMode.title}</div>
                </div>
            `;
            document.body.appendChild(badge);
        }
        
        // 선택 초기화
        function resetSelection() {
            selectedTeacherMode = null;
            selectedStudentMode = null;
            
            // 모든 선택 상태 제거
            document.querySelectorAll('.mode-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // 학생 모드 박스와 액션 버튼 숨기기
            document.getElementById('studentModeBox').style.display = 'none';
            document.getElementById('actionButtonsBox').style.display = 'none';
            
            // 배지 제거
            const badge = document.getElementById('teacherBadge');
            if (badge) badge.remove();
            
            // 스크롤을 맨 위로
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // 학생 모드 섹션은 이미 PHP에서 생성되므로 이 함수는 사용하지 않음
        function createStudentModeSection() {
            // PHP에서 이미 렌더링됨
            console.log('Student mode section already rendered by PHP');
        }
        
        // 선생님 모드로 돌아가기
        function backToTeacherMode() {
            selectedTeacherMode = null;
            selectedStudentMode = null;
            document.getElementById('teacherModeSelection').style.display = 'block';
            const studentSection = document.getElementById('studentModeSelection');
            if (studentSection) {
                studentSection.style.display = 'none';
            }
            document.querySelectorAll('#teacherModeGrid .mode-card').forEach(card => {
                card.classList.remove('selected');
            });
        }

        // 채팅 시작 버튼 상태 업데이트
        function updateSaveButton() {
            const startBtn = document.getElementById('startChatBtn');
            if (selectedTeacherMode && selectedStudentMode) {
                startBtn.disabled = false;
            } else {
                startBtn.disabled = true;
            }
        }

        // 변환 데모 정보 업데이트
        function updateTransformDemo() {
            const teacherModeDisplay = document.getElementById('currentTeacherMode');
            const studentModeDisplay = document.getElementById('currentStudentMode');
            const transformBtn = document.getElementById('transformBtn');
            
            teacherModeDisplay.textContent = selectedTeacherMode ? teacherModes[selectedTeacherMode].title : '선택되지 않음';
            studentModeDisplay.textContent = selectedStudentMode ? studentModes[selectedStudentMode].title : '선택되지 않음';
            
            if (selectedTeacherMode && selectedStudentMode) {
                transformBtn.disabled = false;
            } else {
                transformBtn.disabled = true;
            }
        }

        // 채팅 시작 - 전역 함수로 등록
        window.startChat = async function() {
            if (!selectedTeacherMode || !selectedStudentMode) {
                alert('선생님 모드와 학생 모드를 모두 선택해주세요.');
                return;
            }
            
            const startBtn = document.getElementById('startChatBtn');
            if (startBtn) {
                startBtn.disabled = true;
                startBtn.innerHTML = '<span>⏳</span><span>설정 저장 중...</span>';
            }
            
            const formData = new FormData();
            formData.append('action', 'save_modes');
            formData.append('teacher_mode', selectedTeacherMode);
            formData.append('student_mode', selectedStudentMode);
            formData.append('student_id', '<?php echo $studentid; ?>');
            
            try {
                const response = await fetch('selectmode.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // 채팅 화면으로 전환
                    window.location.href = `chat.php?student_id=<?php echo $studentid; ?>`;
                } else {
                    alert('설정 저장에 실패했습니다: ' + result.message);
                    if (startBtn) {
                        startBtn.disabled = false;
                        startBtn.innerHTML = '<span>💬</span><span>채팅 시작하기</span>';
                    }
                }
            } catch (error) {
                alert('오류가 발생했습니다: ' + error.message);
                if (startBtn) {
                    startBtn.disabled = false;
                    startBtn.innerHTML = '<span>💬</span><span>채팅 시작하기</span>';
                }
            }
        }

        // 메시지 변환
        async function transformMessage() {
            const messageInput = document.getElementById('teacherMessageInput');
            const message = messageInput.value.trim();
            
            if (!message) {
                alert('메시지를 입력해주세요.');
                return;
            }
            
            if (!selectedTeacherMode || !selectedStudentMode) {
                alert('선생님 모드와 학생 모드가 선택되지 않았습니다.');
                return;
            }
            
            const transformBtn = document.getElementById('transformBtn');
            transformBtn.disabled = true;
            transformBtn.innerHTML = '<span>⏳</span><span>변환 중...</span>';
            
            const formData = new FormData();
            formData.append('action', 'transform_message');
            formData.append('message', message);
            formData.append('teacher_mode', selectedTeacherMode);
            formData.append('student_mode', selectedStudentMode);
            
            try {
                const response = await fetch('selectmode.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const transformedResult = document.getElementById('transformedResult');
                    const transformedContent = document.getElementById('transformedMessageContent');
                    
                    transformedContent.innerHTML = `
                        <div style="margin-bottom: 15px;">
                            <strong>원본:</strong> ${message}
                        </div>
                        <div>
                            <strong>변환됨:</strong> ${result.transformed_message}
                        </div>
                    `;
                    
                    transformedResult.style.display = 'block';
                } else {
                    alert('메시지 변환에 실패했습니다.');
                }
            } catch (error) {
                alert('변환 중 오류가 발생했습니다: ' + error.message);
            } finally {
                transformBtn.disabled = false;
                transformBtn.innerHTML = '<span>🤖</span><span>메시지 변환하기</span>';
            }
        }

        // 테스트 함수 (개발용)
        function testModeSelection() {
            // 학생 모드 박스 강제 표시
            const studentModeBox = document.getElementById('studentModeBox');
            if (studentModeBox) {
                console.log('테스트: 학생 모드 박스 강제 표시');
                studentModeBox.style.display = 'block';
                studentModeBox.style.visibility = 'visible';
                studentModeBox.style.opacity = '1';
                studentModeBox.style.transform = 'none';
                
                // 선생님 모드 선택 시뮬레이션
                selectTeacherMode('exam');
            } else {
                alert('studentModeBox를 찾을 수 없습니다!');
            }
        }

        // 선택된 모드 스타일 CSS 추가
        const style = document.createElement('style');
        style.textContent = `
                    .mode-card.selected {
            border: 3px solid #22c55e !important;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.4) !important;
            transform: scale(1.05) !important;
        }
        
        .mode-card.selected::after {
            content: '✓';
            position: absolute;
            top: 20px;
            right: 20px;
            background: #22c55e;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.4);
        }
        `;
        document.head.appendChild(style);
        
        // 디버그 함수
        function debugGridDisplay() {
            const teacherGrid = document.getElementById('teacherModeGrid');
            const studentGrid = document.getElementById('studentModeGrid');
            
            if (teacherGrid) {
                const teacherCards = teacherGrid.querySelectorAll('.mode-card');
                const teacherStyle = window.getComputedStyle(teacherGrid);
                console.log('=== Teacher Grid Debug ===');
                console.log('Grid Element Found:', !!teacherGrid);
                console.log('Display:', teacherStyle.display);
                console.log('Grid Template Columns:', teacherStyle.gridTemplateColumns);
                console.log('Number of Cards:', teacherCards.length);
                console.log('Card Classes:', Array.from(teacherCards).map(c => c.className));
            }
            
            if (studentGrid) {
                const studentCards = studentGrid.querySelectorAll('.mode-card');
                const studentStyle = window.getComputedStyle(studentGrid);
                console.log('=== Student Grid Debug ===');
                console.log('Grid Element Found:', !!studentGrid);
                console.log('Display:', studentStyle.display);
                console.log('Grid Template Columns:', studentStyle.gridTemplateColumns);
                console.log('Number of Cards:', studentCards.length);
                console.log('Card Classes:', Array.from(studentCards).map(c => c.className));
            }
        }
        
        // 페이지 로드 시 초기화
        window.addEventListener('load', function() {
            console.log('페이지 로드 완료. 역할:', '<?php echo $role; ?>');
            console.log('학생 ID:', '<?php echo $studentid; ?>');
            
            // 디버그 정보 출력
            setTimeout(debugGridDisplay, 100);
            
            <?php if ($role == 'teacher'): ?>
                // 선생님인 경우
                console.log('선생님 모드 확인');
                
                // 렌더링된 카드 수 확인
                setTimeout(() => {
                    const teacherCards = document.querySelectorAll('#teacherModeGrid .mode-card').length;
                    const studentCards = document.querySelectorAll('#studentModeGrid .mode-card').length;
                    console.log(`렌더링된 카드: 선생님 ${teacherCards}개, 학생 ${studentCards}개`);
                    
                    // 각 카드 정보 출력
                    document.querySelectorAll('#teacherModeGrid .mode-card').forEach((card, index) => {
                        console.log(`선생님 카드 ${index + 1}: ${card.className}`);
                    });
                }, 100);
                
                // 기존 설정이 있으면 선택 상태 복원
                <?php if ($existing_modes): ?>
                selectedTeacherMode = '<?php echo $existing_modes->teacher_mode; ?>';
                selectedStudentMode = '<?php echo $existing_modes->student_mode; ?>';
                console.log('기존 설정 복원:', selectedTeacherMode, selectedStudentMode);
                updateSaveButton();
                updateTransformDemo();
                
                // 기존 설정이 있으면 즉시 선택 상태 표시
                setTimeout(() => {
                    const teacherCard = document.querySelector(`#teacherModeGrid .mode-card.${selectedTeacherMode}`);
                    const studentCard = document.querySelector(`#studentModeGrid .mode-card.${selectedStudentMode}`);
                    if (teacherCard) teacherCard.classList.add('selected');
                    if (studentCard) studentCard.classList.add('selected');
                    
                    // 기존 설정이 있으면 학생 모드 박스도 표시
                    selectTeacherMode(selectedTeacherMode);
                }, 100);
                <?php endif; ?>
            <?php else: ?>
                // 학생인 경우 디버그 정보만 출력
                console.log('학생 모드 - PHP로 렌더링된 카드 사용');
                // updateModeCards() 함수 호출 제거 - PHP로 이미 렌더링됨
            <?php endif; ?>
        });
    </script>
</body>
</html>