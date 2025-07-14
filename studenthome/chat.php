<?php
include_once("/home/moodle/public_html/moodle/config.php");
include_once("config.php"); // OpenAI API 설정 포함
global $DB, $USER;
require_login();

$student_id = $_GET["student_id"] ?? $_POST["student_id"];
$teacher_id = $USER->id;
 
// 페르소나 모드 가져오기
$persona_modes = $DB->get_record('persona_modes', 
    array('teacher_id' => $teacher_id, 'student_id' => $student_id));

// AJAX 요청 처리 (JSON 응답)
if (isset($_POST['action']) && $_POST['action'] == 'send_message') {
    header('Content-Type: application/json');
    
    if (!$persona_modes) {
        echo json_encode(['success' => false, 'message' => '페르소나 모드가 설정되지 않았습니다.']);
        exit;
    }
    $message = $_POST['message'];
    $room_id = $teacher_id . '_' . $student_id;
    
    try {
        // 먼저 테이블 존재 여부 확인
        $table_exists = false;
        try {
            $DB->count_records('alt42_chat_messages');
            $table_exists = true;
        } catch (Exception $e) {
            error_log("alt42_chat_messages 테이블이 존재하지 않음: " . $e->getMessage());
        }
        
        if (!$table_exists) {
            // 테이블이 없으면 생성
            $sql_create = "CREATE TABLE IF NOT EXISTS {alt42_chat_messages} (
                id BIGINT(10) AUTO_INCREMENT PRIMARY KEY,
                room_id VARCHAR(100) NOT NULL,
                sender_id BIGINT(10) NOT NULL,
                receiver_id BIGINT(10) NOT NULL,
                message_type ENUM('original', 'transformed') DEFAULT 'original',
                message_content TEXT NOT NULL,
                sent_at BIGINT(10) NOT NULL,
                read_at BIGINT(10) DEFAULT NULL,
                INDEX idx_room_id (room_id),
                INDEX idx_sent_at (sent_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $DB->execute($sql_create);
            error_log("alt42_chat_messages 테이블 생성 완료");
        }
        
        // 원본 메시지 저장
        $original_msg = new stdClass();
        $original_msg->room_id = $room_id;
        $original_msg->sender_id = (int)$teacher_id;
        $original_msg->receiver_id = (int)$student_id;
        $original_msg->message_type = 'original';
        $original_msg->message_content = $message;
        $original_msg->sent_at = time();
        
        error_log("원본 메시지 저장 시도: " . json_encode($original_msg));
        $original_id = $DB->insert_record('alt42_chat_messages', $original_msg);
        error_log("원본 메시지 저장 성공, ID: " . $original_id);
        
        // 메시지 변환 함수 호출
        if (!function_exists('transformMessageWithOpenAI')) {
            // transformMessageWithOpenAI 함수를 selectmode.php에서 가져옴
            function transformMessageWithOpenAI($message, $teacher_mode, $student_mode) {
                // config.php에서 API 키와 모델 정보 가져오기
                $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null;
                $model = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4o';
                
                if (!$api_key) {
                    return $message; // API 키가 없으면 원본 반환
                }
                
                $mode_descriptions = [
                    'curriculum' => '체계적이고 계획적인 어조',
                    'exam' => '긴장감 있고 동기부여적인 어조',
                    'custom' => '친근하고 격려하는 어조',
                    'mission' => '게임처럼 도전적이고 즉각적인 어조',
                    'reflection' => '사려깊고 질문을 유도하는 어조',
                    'selfled' => '자율성을 존중하는 제안형 어조'
                ];
                
                $system_prompt = "당신은 선생님의 메시지를 학생의 학습 스타일에 맞게 변환하는 전문 AI입니다.\n\n선생님 모드: {$teacher_mode} ({$mode_descriptions[$teacher_mode]})\n학생 모드: {$student_mode} ({$mode_descriptions[$student_mode]})\n\n변환 원칙:\n1. 핵심 메시지와 의도는 완전히 유지\n2. 학생 모드에 맞는 어조와 표현으로 변경\n3. 구체적이고 실용적인 표현 사용\n4. 한국어로 자연스럽게 표현\n5. 변환된 메시지만 출력 (설명 없이)\n\n원본 메시지를 학생에게 맞게 변환해주세요:";
                
                $data = [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $system_prompt],
                        ['role' => 'user', 'content' => $message]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500
                ];
                
                $ch = curl_init('https://api.openai.com/v1/chat/completions');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $api_key,
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                curl_close($ch);
                
                if ($response) {
                    $result = json_decode($response, true);
                    if (isset($result['choices'][0]['message']['content'])) {
                        return trim($result['choices'][0]['message']['content']);
                    }
                }
                
                return $message; // 실패 시 원본 반환
            }
        }
        
        $transformed_message = transformMessageWithOpenAI($message, $persona_modes->teacher_mode, $persona_modes->student_mode);
        
        // 변환된 메시지 저장
        $transformed_msg = new stdClass();
        $transformed_msg->room_id = $room_id;
        $transformed_msg->sender_id = (int)$teacher_id;
        $transformed_msg->receiver_id = (int)$student_id;
        $transformed_msg->message_type = 'transformed';
        $transformed_msg->message_content = $transformed_message;
        $transformed_msg->sent_at = time();
        
        error_log("변환된 메시지 저장 시도: " . json_encode($transformed_msg));
        $transformed_id = $DB->insert_record('alt42_chat_messages', $transformed_msg);
        error_log("변환된 메시지 저장 성공, ID: " . $transformed_id);
        
        // 변환 이력 저장 (message_transformations 테이블 생성 확인)
        try {
            $DB->count_records('message_transformations');
        } catch (Exception $e) {
            // 테이블이 없으면 생성
            $sql_trans = "CREATE TABLE IF NOT EXISTS {message_transformations} (
                id BIGINT(10) AUTO_INCREMENT PRIMARY KEY,
                teacher_id BIGINT(10) NOT NULL,
                student_id BIGINT(10) NOT NULL,
                original_message TEXT NOT NULL,
                transformed_message TEXT NOT NULL,
                teacher_mode VARCHAR(50) NOT NULL,
                student_mode VARCHAR(50) NOT NULL,
                transformation_time BIGINT(10) NOT NULL,
                INDEX idx_teacher_student (teacher_id, student_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $DB->execute($sql_trans);
            error_log("message_transformations 테이블 생성 완료");
        }
        
        $transformation = new stdClass();
        $transformation->teacher_id = (int)$teacher_id;
        $transformation->student_id = (int)$student_id;
        $transformation->original_message = $message;
        $transformation->transformed_message = $transformed_message;
        $transformation->teacher_mode = $persona_modes->teacher_mode;
        $transformation->student_mode = $persona_modes->student_mode;
        $transformation->transformation_time = time();
        
        error_log("변환 이력 저장 시도: " . json_encode($transformation));
        $trans_id = $DB->insert_record('message_transformations', $transformation);
        error_log("변환 이력 저장 성공, ID: " . $trans_id);
        
        echo json_encode(['success' => true, 'transformed_message' => $transformed_message]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '메시지 처리 중 오류: ' . $e->getMessage()]);
    }
    exit;
}

// 채팅 메시지 가져오기 (AJAX)
if (isset($_GET['action']) && $_GET['action'] == 'get_messages') {
    header('Content-Type: application/json');
    
    try {
        $room_id = $teacher_id . '_' . $student_id;
        $messages = $DB->get_records_sql("SELECT * FROM {alt42_chat_messages} WHERE room_id = ? ORDER BY sent_at ASC", 
            array($room_id));
        
        echo json_encode(['success' => true, 'messages' => array_values($messages)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '메시지 조회 오류: ' . $e->getMessage()]);
    }
    exit;
}

// HTML 페이지 표시를 위한 페르소나 모드 확인
if (!$persona_modes) {
    echo "<script>alert('페르소나 모드가 설정되지 않았습니다. 모드 선택 페이지로 이동합니다.'); window.location.href='selectmode.php?userid=$student_id';</script>";
    exit;
}

$userrole = $DB->get_record_sql("SELECT data FROM mdl_user_info_data where userid='$USER->id' AND fieldid='22'");
$role = $userrole->data;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI 페르소나 매칭 채팅</title>
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
        }

        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }

        .persona-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .mode-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .teacher-mode {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.5);
            color: #60a5fa;
        }

        .student-mode {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #22c55e;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message-pair {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .message {
            max-width: 70%;
            padding: 15px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }

        .message.teacher-original {
            align-self: flex-end;
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            opacity: 0.7;
        }

        .message.student-transformed {
            align-self: flex-start;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            border-left: 4px solid #10b981;
        }

        .message.received {
            align-self: flex-start;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .message-meta {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }

        .chat-input {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }

        .input-container {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .message-input {
            flex: 1;
            padding: 15px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 25px;
            background: rgba(255,255,255,0.1);
            color: white;
            resize: none;
            min-height: 50px;
            max-height: 120px;
        }

        .message-input::placeholder {
            color: rgba(255,255,255,0.6);
        }

        .send-button {
            padding: 15px 25px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            border-radius: 25px;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .send-button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.4);
        }

        .send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .transformation-indicator {
            position: absolute;
            top: -10px;
            right: 10px;
            background: #22c55e;
            color: white;
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 10px;
            font-weight: bold;
        }

        .role-label {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .teacher-label {
            text-align: right;
            color: #6b7280;
        }

        .student-label {
            text-align: left;
            color: #22c55e;
        }

        .back-link {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #9ca3af;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <a href="selectmode.php?userid=<?php echo $student_id; ?>" class="back-link">← 모드 설정으로 돌아가기</a>
        
        <div class="chat-header">
            <h2 style="margin-bottom: 15px;">AI 페르소나 매칭 채팅</h2>
            <div class="persona-info">
                <div class="mode-badge teacher-mode">
                    선생님 모드: <?php echo $persona_modes->teacher_mode; ?>
                </div>
                <div style="font-size: 24px;">🤖</div>
                <div class="mode-badge student-mode">
                    학생 모드: <?php echo $persona_modes->student_mode; ?>
                </div>
            </div>
            <p style="color: #9ca3af; font-size: 14px; text-align: center;">
                선생님의 메시지가 학생의 학습 스타일에 맞게 자동 변환됩니다
            </p>
        </div>

        <div class="chat-messages" id="chatMessages">
            <!-- 메시지들이 여기에 동적으로 추가됩니다 -->
        </div>

        <div class="chat-input">
            <div class="input-container">
                <textarea 
                    id="messageInput" 
                    class="message-input" 
                    placeholder="메시지를 입력하세요... (Enter로 전송, Shift+Enter로 줄바꿈)"
                    rows="1"></textarea>
                <button id="sendButton" class="send-button" onclick="sendMessage()">전송</button>
            </div>
        </div>
    </div>

    <script>
        let teacherId = <?php echo $teacher_id; ?>;
        let studentId = <?php echo $student_id; ?>;
        
        // 메시지 전송 - 전역 함수로 정의
        window.sendMessage = async function() {
            console.log('sendMessage 함수 호출됨');
            
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            
            if (!messageInput || !sendButton) {
                console.error('메시지 입력창 또는 전송 버튼을 찾을 수 없습니다');
                alert('페이지 요소를 찾을 수 없습니다. 페이지를 새로고침해보세요.');
                return;
            }
            
            const message = messageInput.value.trim();
            console.log('입력된 메시지:', message);
            
            if (!message) return;
            
            messageInput.value = '';
            sendButton.disabled = true;
            sendButton.textContent = '변환 중...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('message', message);
                formData.append('student_id', studentId);
                
                const response = await fetch('chat.php', {
                    method: 'POST',
                    body: formData
                });
                
                // 응답 상태 확인
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const text = await response.text();
                console.log('서버 응답:', text);
                
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('JSON 파싱 오류:', e);
                    throw new Error('서버 응답을 처리할 수 없습니다: ' + text.substring(0, 100));
                }
                
                if (result.success) {
                    // 메시지 쌍 추가 (원본 + 변환)
                    addMessagePairToChat(message, result.transformed_message);
                } else {
                    alert('메시지 전송에 실패했습니다: ' + (result.message || '알 수 없는 오류'));
                }
            } catch (error) {
                console.error('전송 오류:', error);
                alert('오류가 발생했습니다: ' + error.message);
            } finally {
                sendButton.disabled = false;
                sendButton.textContent = '전송';
            }
        }
        
        // 메시지 쌍 추가 (선생님 원본 + 학생용 변환)
        function addMessagePairToChat(originalMessage, transformedMessage) {
            const chatMessages = document.getElementById('chatMessages');
            const messagePairElement = document.createElement('div');
            messagePairElement.className = 'message-pair';
            
            const timestamp = new Date().toLocaleTimeString();
            
            messagePairElement.innerHTML = `
                <div class="role-label teacher-label">선생님 (원본)</div>
                <div class="message teacher-original">
                    <div>${originalMessage}</div>
                    <div class="message-meta">${timestamp}</div>
                </div>
                
                <div class="role-label student-label">학생에게 전달 (AI 변환)</div>
                <div class="message student-transformed">
                    <div class="transformation-indicator">AI 변환됨</div>
                    <div>${transformedMessage}</div>
                    <div class="message-meta">${timestamp} • 전달 완료</div>
                </div>
            `;
            
            chatMessages.appendChild(messagePairElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // 채팅에 메시지 추가 (기존 메시지 로드용)
        function addMessageToChat(message) {
            const chatMessages = document.getElementById('chatMessages');
            const messageElement = document.createElement('div');
            
            const isTransformed = message.message_type === 'transformed';
            const messageClass = message.sender_id == teacherId ? 'sent' : 'received';
            
            messageElement.className = `message ${messageClass} ${isTransformed ? 'transformed' : ''}`;
            messageElement.innerHTML = `
                ${isTransformed ? '<div class="transformation-indicator">AI 변환됨</div>' : ''}
                <div>${message.message_content}</div>
                <div class="message-meta">
                    ${new Date(message.sent_at * 1000).toLocaleTimeString()}
                    ${isTransformed ? '• 학생에게 전달' : '• 원본'}
                </div>
            `;
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // 기존 메시지 로드
        async function loadMessages() {
            try {
                const response = await fetch(`chat.php?action=get_messages&student_id=${studentId}`);
                const result = await response.json();
                
                if (result.success && result.messages) {
                    result.messages.forEach(message => {
                        addMessageToChat(message);
                    });
                }
            } catch (error) {
                console.error('메시지 로드 실패:', error);
            }
        }
        
        // 페이지 로드 시 초기화
        window.addEventListener('load', function() {
            console.log('페이지 로드 완료');
            
            // 기존 메시지 로드
            loadMessages();
            
            // Enter 키 이벤트 리스너 설정
            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                messageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        console.log('Enter 키로 전송 시도');
                        sendMessage();
                    }
                });
                console.log('Enter 키 이벤트 리스너 설정 완료');
            } else {
                console.error('messageInput 요소를 찾을 수 없습니다');
            }
            
            // 전송 버튼 이벤트 리스너도 추가 (onclick과 중복 방지용)
            const sendButton = document.getElementById('sendButton');
            if (sendButton) {
                console.log('전송 버튼 찾음, 클릭 이벤트 리스너 추가');
                sendButton.addEventListener('click', function() {
                    console.log('전송 버튼 클릭됨');
                    sendMessage();
                });
            } else {
                console.error('sendButton 요소를 찾을 수 없습니다');
            }
        });
        
        // 주기적으로 새 메시지 확인 (간단한 polling)
        setInterval(loadMessages, 5000);
    </script>
</body>
</html>