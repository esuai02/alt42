<?php
require_once("/home/moodle/public_html/moodle/config.php");
global $DB, $USER;

// 에러 출력 방지
error_reporting(0);
ini_set('display_errors', 0);

// JSON 응답을 보장하기 위해 헤더 먼저 설정
header('Content-Type: application/json; charset=utf-8');

// 로그인 체크
if (!isloggedin()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
} 

$action = $_POST['action'] ?? '';

// 디버깅용: 모든 POST 데이터 확인
error_log("patternbank_ajax.php - All POST data: " . json_encode($_POST));

if ($action === 'save_problem') {
    try {
        // 필수 필드 검증
        if (!isset($_POST['cntid']) || !isset($_POST['cnttype']) || !isset($_POST['question']) || !isset($_POST['solution'])) {
            throw new Exception('Required fields missing');
        }
        
        // 디버깅 정보
        error_log("Save problem - POST data: " . json_encode($_POST));
        error_log("Type field received: " . (isset($_POST['type']) ? $_POST['type'] : 'NOT SET'));
        
        $problem = new stdClass();  
        $problem->authorid = $USER->id;   
        $problem->cntid = $_POST['cntid'];   
        $problem->cnttype = $_POST['cnttype'];     
        $problem->question = $_POST['question']; 
        $problem->solution = $_POST['solution'];
        // choices가 있으면 사용, 없으면 inputanswer 사용
        if (isset($_POST['choices'])) {
            $problem->inputanswer = $_POST['choices'];
        } else {
            $problem->inputanswer = $_POST['inputanswer'] ?? null;
        }
        $problem->type = $_POST['type'] ?? 'similar';  // 기본값은 'similar'
        $problem->timecreated = time(); 
        $problem->timemodified = time();
        
        // NULL 값들 
        $problem->qstnimgurl = null; 
        $problem->solimgurl = null;
        $problem->fullqstnimgurl = null;
        $problem->fullsolimgurl = null;
          

        // 디버깅용 로그 
        error_log("Problem object: " . json_encode($problem)); 
        error_log("Type value: " . $problem->type);
        
        // 테이블 구조 확인
        $columns = $DB->get_columns('abessi_patternbank');
        $has_type_field = false;
        foreach ($columns as $column) {
            if ($column->name === 'type') {
                $has_type_field = true;
                error_log("Type field found in table - Type: " . $column->type . ", Max length: " . $column->max_length);
                break;
            }
        }
        if (!$has_type_field) {
            error_log("WARNING: type field not found in abessi_patternbank table!");
        }
        
        $id = $DB->insert_record('abessi_patternbank', $problem);
        
        error_log("Inserted ID: " . $id);
        
        // 삽입된 데이터 확인
        $inserted = $DB->get_record('abessi_patternbank', ['id' => $id]);
        error_log("Inserted record type: " . (isset($inserted->type) ? $inserted->type : 'NULL'));
        
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Problem saved successfully', 'type_saved' => $problem->type, 'type_in_db' => isset($inserted->type) ? $inserted->type : 'NULL']);
    } catch (Exception $e) {
        error_log("Save problem error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'message' => $e->getMessage()]);
    }
    exit; 
}
 
if ($action === 'get_problem') {
    try {
        $id = $_POST['id'];
        $problem = $DB->get_record('abessi_patternbank', ['id' => $id]);
        
        if ($problem) {
            echo json_encode([
                'id' => $problem->id,
                'question' => $problem->question,
                'solution' => $problem->solution,
                'inputanswer' => $problem->inputanswer,
                'qstnimgurl' => $problem->qstnimgurl,
                'solimgurl' => $problem->solimgurl,
                'cntid' => $problem->cntid,
                'cnttype' => $problem->cnttype,
                'type' => $problem->type ?? 'similar'  
                
            ]);
        } else {
            echo json_encode(['error' => 'Problem not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'load_problems') {
    try {
        $cntid = $_POST['cntid'];
        $cnttype = $_POST['cnttype'];
        $problems = $DB->get_records('abessi_patternbank', ['cntid' => $cntid, 'cnttype' => $cnttype]);
        
        $result = [];
        foreach ($problems as $problem) {
            $result[] = [
                'id' => $problem->id,
                'question' => $problem->question,
                'solution' => $problem->solution,
                'inputanswer' => $problem->inputanswer,
                'type' => $problem->type ?? 'similar'
            ];
        }
        
        echo json_encode(['success' => true, 'problems' => $result]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'test') {
    echo json_encode(['success' => true, 'message' => 'Server connection test successful']);
    exit;
}

if ($action === 'update_problem') {
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('Problem ID missing');
        }
        
        $id = intval($_POST['id']); // ID를 정수로 변환
        error_log("Update problem - ID: $id");
        
        // 기존 레코드 가져오기
        $problem = $DB->get_record('abessi_patternbank', ['id' => $id]);
        
        if (!$problem) {
            throw new Exception('Problem not found with ID: ' . $id);
        }
        
        // 작성자 권한 확인
        if ($problem->authorid != $USER->id && !is_siteadmin()) {
            throw new Exception('Permission denied: You can only edit your own problems');
        }
        
        // 테이블 구조 확인 (디버깅용)
        $columns = $DB->get_columns('abessi_patternbank');
        $column_names = array_keys($columns);
        error_log("Table columns: " . json_encode($column_names));
        error_log("Original record: " . json_encode($problem));
        
        // 기존 레코드의 모든 필드를 복사하여 시작
        $updateData = clone $problem;
        
        // 업데이트할 필드만 변경
        if (isset($_POST['question'])) {
            $updateData->question = trim($_POST['question']);
        }
        
        if (isset($_POST['solution'])) {
            $updateData->solution = trim($_POST['solution']);
        }
        
        // choices가 있으면 업데이트
        if (isset($_POST['choices'])) {
            $updateData->inputanswer = $_POST['choices'];
        }
        
        // 수정 시간 업데이트
        $updateData->timemodified = time();
        
        error_log("Update data: " . json_encode($updateData));
        
        // 데이터베이스 업데이트
        try {
            // 데이터 타입 확인
            error_log("Update data types check:");
            error_log("ID type: " . gettype($updateData->id) . ", value: " . $updateData->id);
            error_log("authorid type: " . gettype($updateData->authorid) . ", value: " . $updateData->authorid);
            error_log("cntid type: " . gettype($updateData->cntid) . ", value: " . $updateData->cntid);
            error_log("cnttype type: " . gettype($updateData->cnttype) . ", value: " . $updateData->cnttype);
            
            // NULL 필드 확인
            foreach ($updateData as $key => $value) {
                if (is_null($value)) {
                    error_log("NULL field found: $key");
                }
            }
            
            $success = $DB->update_record('abessi_patternbank', $updateData);
            
            if ($success) {
                error_log("Problem updated successfully");
                echo json_encode(['success' => true, 'message' => 'Problem updated successfully']);
            } else {
                throw new Exception('Database update failed');
            }
        } catch (dml_exception $e) {
            error_log("Database error details: " . $e->getMessage());
            error_log("Error debuginfo: " . $e->debuginfo);
            error_log("Error backtrace: " . $e->getTraceAsString());
            
            // 더 자세한 에러 정보 제공
            $error_msg = '데이터베이스 쓰기 오류';
            if (strpos($e->debuginfo, 'Data too long') !== false) {
                $error_msg = '입력한 데이터가 너무 깁니다';
            } elseif (strpos($e->debuginfo, 'Incorrect integer value') !== false) {
                $error_msg = '잘못된 숫자 형식입니다';
            } elseif (strpos($e->debuginfo, 'Column') !== false && strpos($e->debuginfo, 'cannot be null') !== false) {
                $error_msg = '필수 필드가 비어있습니다';
            }
            
            throw new Exception($error_msg . ': ' . $e->getMessage());
        }
    } catch (Exception $e) {
        error_log("Update problem error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'check_table') {
    try {
        $columns = $DB->get_columns('abessi_patternbank');
        $column_names = [];
        $type_field_info = null;
        
        foreach ($columns as $column) {
            $column_names[] = $column->name;
            if ($column->name === 'type') {
                $type_field_info = [
                    'name' => $column->name,
                    'type' => $column->type,
                    'max_length' => $column->max_length,
                    'not_null' => $column->not_null,
                    'default' => $column->has_default ? $column->default_value : null
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'columns' => $column_names,
            'type_field' => $type_field_info,
            'has_type_field' => !is_null($type_field_info)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'save_analysis') {
    try {
        if (!isset($_POST['cntid']) || !isset($_POST['analysis'])) {
            throw new Exception('Required parameters missing');
        }
        
        $cntid = $_POST['cntid'];
        $analysis = $_POST['analysis'];
        
        error_log("Save analysis - cntid: $cntid, text length: " . strlen($analysis));
        
        // mdl_icontent_pages 테이블 확인
        $page = $DB->get_record('icontent_pages', ['id' => $cntid]);
        
        if (!$page) {
            error_log("Page not found with id: $cntid");
            throw new Exception('Page not found with id: ' . $cntid);
        }
        
        // analysis 필드가 없으면 추가
        $columns = $DB->get_columns('icontent_pages');
        $has_analysis_field = false;
        foreach ($columns as $column) {
            if ($column->name === 'analysis') {
                $has_analysis_field = true;
                break;
            }
        }
        
        if (!$has_analysis_field) {
            error_log("WARNING: analysis field not found in icontent_pages table!");
            // 필드가 없는 경우 reflections0 필드에 저장 (임시)
            $page->reflections0 = $analysis;
        } else {
            $page->analysis = $analysis;
        }
        
        $page->timemodified = time();
        
        $success = $DB->update_record('icontent_pages', $page);
        
        if ($success) {
            error_log("Analysis saved successfully");
            echo json_encode(['success' => true, 'message' => 'Analysis saved successfully']);
        } else {
            throw new Exception('Failed to save analysis');
        }
    } catch (Exception $e) {
        error_log("Save analysis error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage(), 'message' => $e->getMessage()]);
    }
    exit;
}

// 잘못된 액션
error_log("Invalid action received: " . $action);
echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
?>