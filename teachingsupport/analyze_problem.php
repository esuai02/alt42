<?php 
include_once("/home/moodle/public_html/moodle/config.php"); 
require_once(__DIR__ . '/config.php');
global $DB, $USER;
require_login();

header('Content-Type: application/json');

// CORS 헤더 설정 (필요시)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // 이미지 파일 처리
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('이미지 업로드 실패');
    }

    $uploadedFile = $_FILES['image'];
    $problemType = $_POST['problemType'] ?? '';
    $studentId = $_POST['studentId'] ?? '';

    // 이미지를 base64로 인코딩
    $imageData = file_get_contents($uploadedFile['tmp_name']);
    $base64Image = base64_encode($imageData);
    
    // 파일 타입 확인
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
    finfo_close($finfo);

    // 문제 유형에 따른 프롬프트 커스터마이징
    $typeDescriptions = [
        'exam' => '내신 기출문제',
        'school' => '학교 프린트 문제',
        'mathking' => 'MathKing 문제',
        'textbook' => '시중교재 문제'
    ];
    
    $problemTypeDesc = $typeDescriptions[$problemType] ?? '일반 문제';

    // OpenAI API 호출
    $messages = [
        [
            'role' => 'system',
            'content' => '당신은 한국의 우수한 수학 교사입니다. 학생들이 이해하기 쉽도록 단계별로 문제를 해설해주세요. 

중요: 모든 수식은 반드시 LaTeX 형식으로 작성해주세요.
- 인라인 수식: $수식$ (예: $x^2 + 2x + 1 = 0$)
- 별도 줄 수식: $$수식$$ (예: $$\\frac{-b \\pm \\sqrt{b^2-4ac}}{2a}$$)
- 분수는 \\frac{분자}{분모}
- 제곱근은 \\sqrt{내용}
- 지수는 ^{지수}
- 아래첨자는 _{아래첨자}

다음 형식으로 답변해주세요:

[문제 분석]
- 문제 유형과 난이도를 분석

[풀이 과정]
- 단계별로 상세하게 설명
- 각 단계마다 이유와 원리 설명
- 모든 수식은 LaTeX 형식 사용

[정답]
- 최종 답안 제시 (LaTeX 형식)

[핵심 개념]
- 이 문제를 풀기 위해 알아야 할 핵심 개념들

[유사 문제]
- 비슷한 유형의 문제 예시나 연습 방법'
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => "다음 {$problemTypeDesc}를 분석하고 자세히 해설해주세요."
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:{$mimeType};base64,{$base64Image}"
                    ]
                ]
            ]
        ]
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => OPENAI_MODEL, // o3 모델은 아직 사용 불가
        'messages' => $messages,
        'max_tokens' => 2000,
        'temperature' => 0.7
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
        throw new Exception('OpenAI API 호출 실패: HTTP ' . $httpCode . ' - ' . $errorMessage);
    }

    $responseData = json_decode($response, true);
    
    if (!isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception('OpenAI 응답 형식 오류');
    }

    $solution = $responseData['choices'][0]['message']['content'];

    // 데이터베이스에 기록 저장 (선택사항)
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->studentid = $studentId;
    $record->problemtype = $problemType;
    $record->solution = $solution;
    $record->timecreated = time();
    
    // teaching_solutions 테이블이 있다고 가정
    // $DB->insert_record('teaching_solutions', $record);

    echo json_encode([
        'success' => true,
        'solution' => $solution,
        'problemType' => $problemTypeDesc
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>