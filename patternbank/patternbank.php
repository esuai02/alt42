﻿<?php 
 
include_once("/home/moodle/public_html/moodle/config.php"); 
include_once("/home/moodle/public_html/moodle/configwhiteboard.php"); 
 
global $DB, $USER;

$cntid = $_GET["contentsid"]; 
$cnttype = $_GET["contentstype"]; 
$studentid = $_GET["userid"];
$wboardid = $_GET["wboardid"];
$print = $_GET["print"];
 
$thisuser= $DB->get_record_sql("SELECT  lastname, firstname FROM mdl_user WHERE id='$studentid' ");
$stdname=$thisuser->firstname.$thisuser->lastname; 
 
if($cnttype==1) 
    { 
    $cnttext=$DB->get_record_sql("SELECT * FROM mdl_icontent_pages where id='$cntid'  ORDER BY id DESC LIMIT 1");  
    $eventid=1;
    $maintext=$cnttext->maintext;
    if($print==0)$papertest=$cnttext->reflections0;
    else $papertest=$cnttext->reflections1;
  

	$ctext=$cnttext->pageicontent;
	if($cnttext->reflections!=NULL)$reflections=$cnttext->reflections.'<hr>';
	$htmlDom = new DOMDocument;
 
	@$htmlDom->loadHTML($ctext);
	$imageTags = $htmlDom->getElementsByTagName('img');
	$extractedImages = array(); 
	$nimg=0;
	foreach($imageTags as $imageTag)
		{
		$nimg++;
		$imgSrc = $imageTag->getAttribute('src');
		$imgSrc = str_replace(' ', '%20', $imgSrc); 
		if(strpos($imgSrc, 'MATRIX')!= false || strpos($imgSrc, 'MATH')!= false || strpos($imgSrc, 'imgur')!= false)break;
		}
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern Bank Interface</title>
    <!-- MathJax for LaTeX rendering -->
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['\\(', '\\)'], ['$', '$']],
                displayMath: [['\\[', '\\]'], ['$$', '$$']]
            },
            startup: {
                pageReady: () => {
                    return MathJax.startup.defaultPageReady().then(() => {
                        console.log('MathJax initial typesetting complete');
                    });
                }
            }
        };
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f5f6fa;
            color: #2c3e50;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            width: 100%;
        }

        h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 700;
        }

        .exam-button {
            padding: 12px 24px;
            background-color: #9b59b6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .exam-button:hover {
            background-color: #8e44ad;
            transform: translateY(-1px);
        }
        
        .exam-button:hover[onclick*="printSelectedProblems"] {
            background-color: #d35400;
        }

        /* 상단 섹션 */
        .top-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            width: 100%;
            max-width: 1400px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            min-width: 0;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .representative-type {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .type-title {
            font-size: 16px;
            font-weight: 600;
            color: #3498db;
            margin-bottom: 10px;
        }

        .type-content {
            color: #555;
            line-height: 1.8;
        }

        .analysis-text {
            color: #555;
            line-height: 1.8;
            text-align: justify;
            cursor: pointer;
            position: relative;
            min-height: 100px;
        }
        
        .analysis-text:hover {
            background-color: #f9f9f9;
        }
        
        .analysis-text[contenteditable="true"] {
            background-color: #fff;
            border: 2px solid #3498db;
            padding: 10px;
            cursor: text;
        }
        
        .analysis-save-indicator {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 12px;
            color: #27ae60;
            display: none;
        }

        /* 하단 섹션 */
        .bottom-section {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* 문제 블록 스타일 */
        .problem-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-start;
        }

        .problem-block {
            background-color: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
            color: #2c3e50;
            user-select: none; /* 더블클릭 시 텍스트 선택 방지 */
        }

        .problem-block:hover {
            background-color: #e8f4f8;
            border-color: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        .problem-block.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        
        .problem-block.similar {
            border-color: #90EE90;
            border-width: 3px;
        }
        
        .problem-block.similar:hover {
            border-color: #7FDD7F;
        }
        
        .problem-block.modified {
            border-color: #87CEEB;
            border-width: 3px;
        }
        
        .problem-block.modified:hover {
            border-color: #5DADE2;
        }

        /* 툴팁 스타일 */
        .tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 100;
            max-width: 200px;
            white-space: normal;
            text-align: left;
            font-weight: normal;
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #333;
        }

        .problem-block:hover .tooltip {
            opacity: 1;
            visibility: visible;
            bottom: calc(100% + 8px);
        }

        .add-button {
            padding: 12px 20px;
            background-color: #f8f9fa;
            color: #2c3e50;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .add-button.compact {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .generator-button {
            padding: 8px 12px;
            background-color: #f8f9fa;
            color: #2c3e50;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .generator-button:hover {
            background-color: #e9ecef;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .add-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        .add-button:active {
            transform: translateY(0);
        }

        .add-button.similar {
            background-color: #90EE90;
            border-color: #90EE90;
        }

        .add-button.similar:hover {
            background-color: #7FDD7F;
            border-color: #7FDD7F;
        }

        .add-button.variant {
            background-color: #87CEEB;
            border-color: #87CEEB;
        }

        .add-button.variant:hover {
            background-color: #5DADE2;
            border-color: #5DADE2;
        }

        /* 로딩 애니메이션 */
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(44, 62, 80, 0.3);
            border-radius: 50%;
            border-top-color: #2c3e50;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* 모달 스타일 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: #2c3e50;
        }

        .modal h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* 시험지 모달 스타일 */
        .exam-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .exam-content {
            background-color: white;
            margin: 20px auto;
            padding: 0;
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .exam-paper {
            padding: 15mm 25mm;
            font-family: 'Batang', serif;
            color: #000;
            line-height: 1.8;
        }

        .exam-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 20px;
        }

        .exam-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .exam-info {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 14px;
        }

        .student-info {
            display: flex;
            gap: 30px;
        }

        .student-info span {
            display: inline-block;
            min-width: 150px;
            border-bottom: 1px solid #000;
        }

        .exam-section {
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-left: 4px solid #333;
        }

        .exam-problem {
            margin-bottom: 25px;
            padding-left: 20px;
            page-break-inside: avoid;
        }

        .problem-number {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .problem-content {
            margin-bottom: 15px;
            line-height: 2;
        }

        .answer-space {
            height: 60px;
            border: 1px solid #ccc;
            margin-top: 10px;
            padding: 10px;
            background-color: #fafafa;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1001;
        }

        .print-button:hover {
            background-color: #34495e;
        }

        /* 인쇄 스타일 */
        @media print {
            body * {
                visibility: hidden;
            }
            .exam-content,
            .exam-content * {
                visibility: visible;
            }
            .exam-content {
                position: absolute;
                left: 0;
                top: 0;
                margin: 0;
                box-shadow: none;
            }
            .print-button,
            .close {
                display: none !important;
            } 
        } 

        /* 반응형 디자인 */ 
        @media (max-width: 1300px) {
            .bottom-section {
                max-width: 700px;
                margin: 0 auto;
            } 
        }

        @media (max-width: 768px) {
            .top-section {
                grid-template-columns: 1fr;
            }
            .header-section {
                flex-direction: column;
                gap: 20px;
            }
        } 
    </style> 
</head>  
<body>
    <div class="container">
        <div class="header-section"> 
            <h1>KTM 유형별 문제은행</h1> 
            <div style="display: flex; gap: 10px;">
                <button class="exam-button" onclick="printSelectedProblems()" style="background-color: #e67e22;">
                    🖨️ 시험지 인쇄
                </button>
                <button class="exam-button" onclick="printSolutionSheet()" style="background-color: #27ae60;">
                    📖 해설지 인쇄
                </button>
            </div>
        </div>
        
        <!-- 상단 섹션 -->
        <div class="top-section">
            <!-- 대표유형 -->
            <div class="card">
                <h2 class="card-header">📋 대표유형</h2>
                <img src="<?php echo $imgSrc; ?>" alt="대표유형 이미지" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;">
                 
            </div>
            
            <!-- 유형 분석글 -->
            <div class="card">
                <h2 class="card-header">📊 유형 분석</h2>
                <div class="analysis-text" id="analysisText" 
                     ondblclick="enableAnalysisEdit()" 
                     title="더블클릭하여 수정">
                    <?php 
                    // DB에서 analysis 필드 가져오기
                    $analysisText = $cnttext->analysis ?? '수열의 규칙성 문제는 대수적 사고력을 평가하는 핵심 문제 유형입니다. 
이 유형은 주로 등차수열, 등비수열, 피보나치 수열 등의 기본 패턴을 변형하여 출제됩니다.
<br><br>
학생들은 첫 번째로 인접한 항들 간의 차이나 비율을 계산하여 규칙을 찾아야 합니다. 
복잡한 문제의 경우, 두 가지 이상의 규칙이 복합적으로 적용되거나, 
홀수 번째와 짝수 번째 항이 서로 다른 규칙을 따르는 경우도 있습니다.';
                    echo $analysisText;
                    ?>
                </div>
            </div>
        </div> 
        
        <!-- 하단 섹션 -->
        <div class="bottom-section">
            <!-- 통합된 문제 영역 -->
            <div class="card" style="width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <h2 class="card-header" style="margin: 0;">📚 목록</h2>
                    <div class="problem-grid" id="allProblems" style="flex: 1; margin-left: 20px;">
                        <!-- 문제들이 동적으로 로드됩니다 -->
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 30px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                    <div style="display: flex; gap: 5px;">
                        <button class="add-button similar compact" onclick="addSimilarProblem()">
                            <span>➕ 유사문제</span>
                            <div class="loading" id="similarLoading"></div>
                        </button>
                        <a href="https://chatgpt.com/g/g-686b748956188191841231228e5c7f51?model=o4-mini-high" target="_blank" class="generator-button">🔗</a>
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <button class="add-button variant compact" onclick="addModifiedProblem()">
                            <span>➕ 변형문제</span>
                            <div class="loading" id="variantLoading"></div>
                        </button>
                        <a href="https://chatgpt.com/g/g-686b72e14e948191858bf3eec78c7c76?model=o4-mini-high" target="_blank" class="generator-button">🔗</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 일반 모달 -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">새 문제 생성</h3>
            <div class="modal-problem">
                <p id="modalMessage"></p>
            </div>
        </div> 
    </div>    
    
    <!-- JSON 입력 모달 -->
    <div id="jsonModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeJsonModal()">&times;</span>
            <h3>문제 JSON 입력</h3>
            <div style="margin: 20px 0;">
                <label for="jsonInput" style="display: block; margin-bottom: 10px; font-weight: 600;">JSON 데이터를 입력하세요:</label>
                <textarea id="jsonInput" style="width: 100%; height: 350px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 14px;" placeholder='{
  "문항": "문제 내용 (LaTeX 수식: $x^2$ 또는 \\(x^2\\) 형식 사용 가능)",
  "선택지": [
    "① 선택지 1",
    "② 선택지 2",
    "③ 선택지 3",
    "④ 선택지 4",
    "⑤ 선택지 5"
  ],
  "해설": "해설 내용 (LaTeX 수식: $x^2$ 또는 \\(x^2\\) 형식 사용 가능)"
}'></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="closeJsonModal()" style="padding: 10px 20px; background-color: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer;">취소</button>
                <button onclick="saveJsonProblem()" style="padding: 10px 20px; background-color: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">저장</button>
            </div>
        </div>
    </div>

    <!-- 문제 상세 정보 모달 -->
    <div id="problemDetailModal" class="modal">
        <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <span class="close" onclick="closeProblemDetailModal()">&times;</span>
            <h3>문제 상세 정보</h3>
            <div style="margin: 20px 0;">
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #2c3e50; margin-bottom: 10px;">문제</h4>
                    <div id="problemQuestion" contenteditable="true" style="padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 10px; font-size: 16px; line-height: 1.8; border: 2px solid transparent; transition: border-color 0.3s;" 
                         onfocus="this.style.borderColor='#3498db'" 
                         onblur="this.style.borderColor='transparent'"></div>
                    <div id="problemChoices" contenteditable="true" style="padding: 15px; background-color: #fff; border: 1px solid #e0e0e0; border-radius: 5px; margin-bottom: 10px; transition: border-color 0.3s;"
                         onfocus="this.style.borderColor='#3498db'" 
                         onblur="this.style.borderColor='#e0e0e0'"></div>
                    <div id="problemQuestionImage" style="margin-bottom: 10px;"></div>
                </div>
                <div>
                    <h4 style="color: #2c3e50; margin-bottom: 10px;">해설</h4>
                    <div id="problemSolution" contenteditable="true" style="padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 10px; font-size: 16px; line-height: 1.8; border: 2px solid transparent; transition: border-color 0.3s;"
                         onfocus="this.style.borderColor='#3498db'" 
                         onblur="this.style.borderColor='transparent'"></div>
                    <div id="problemSolutionImage" style="margin-bottom: 10px;"></div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: space-between;">
                    <button onclick="showJsonEditor()" style="padding: 10px 20px; background-color: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">📝 JSON 교체</button>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="saveProblemChanges()" style="padding: 10px 20px; background-color: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">💾 저장</button>
                        <button onclick="closeProblemDetailModal()" style="padding: 10px 20px; background-color: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">취소</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 시험지 모달 -->
    <div id="examModal" class="exam-modal">
        <button class="print-button" onclick="printExam()">🖨️ 인쇄하기</button>
        <span class="close" style="position: fixed; top: 20px; left: 20px; z-index: 1001; color: white; font-size: 40px;" onclick="closeExamModal()">&times;</span>
        <div class="exam-content">
            <div class="exam-paper" id="examPaper">
                <!-- 시험지 내용이 여기 동적으로 생성됩니다 -->
            </div>
        </div>
    </div>

    <script>
        // PHP 변수를 JavaScript로 전달
        const PHP_VARS = {
            cntid: '<?php echo $cntid; ?>',
            cnttype: '<?php echo $cnttype; ?>',
            userid: '<?php echo $USER->id; ?>'
        };

        // 원본 문제 정보
        const originalProblem = {
            type: "수열의 규칙성 찾기",
            pattern: "등비수열",
            example: "2, 4, 8, 16, ?",
            answer: "32",
            difficulty: "중급"
        };

        // 페이지 로드 시 문제들 불러오기
        window.addEventListener('DOMContentLoaded', async function() {
            // 테이블 구조 확인
            await checkTableStructure();
            await loadProblems();
        });
        
        // 테이블 구조 확인 함수
        async function checkTableStructure() {
            const formData = new FormData();
            formData.append('action', 'check_table');
            
            try {
                const response = await fetch('patternbank_ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                console.log('Table structure:', result);
                
                if (!result.has_type_field) {
                    console.error('WARNING: type field not found in database table!');
                    alert('경고: 데이터베이스 테이블에 type 필드가 없습니다. 관리자에게 문의하세요.');
                } else {
                    console.log('Type field info:', result.type_field);
                }
            } catch (e) {
                console.error('Failed to check table structure:', e);
            }
        }

        // DB에서 문제들 불러오기
        async function loadProblems() {
            const formData = new FormData();
            formData.append('action', 'load_problems');
            formData.append('cntid', '<?php echo $cntid; ?>');
            formData.append('cnttype', '<?php echo $cnttype; ?>');
            try {
                const response = await fetch('patternbank_ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                // 응답이 JSON인지 확인
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    return;
                }
                
                const result = await response.json();
                
                if (result.success && result.problems) {
                    let problemCount = 1;
                    
                    result.problems.forEach(problem => {
                        addProblemBlock('allProblems', {
                            id: problem.id,
                            number: problemCount++,
                            inputanswer: problem.inputanswer,
                            question: problem.question,
                            solution: problem.solution,
                            type: problem.type || 'similar'
                        });
                    });
                }
            } catch (e) {
                console.error('문제 로드 중 오류:', e);
            }
        }

        // 현재 문제 타입 저장
        window.currentProblemType = '';

        // 유사문제 추가
        async function addSimilarProblem() {
            window.currentProblemType = 'similar';
            document.getElementById('jsonModal').style.display = 'block';
            document.getElementById('jsonInput').value = '';
        }

        // 변형문제 추가
        async function addModifiedProblem() {
            window.currentProblemType = 'modified';
            document.getElementById('jsonModal').style.display = 'block';
            document.getElementById('jsonInput').value = '';
        }
 
        // 유사문제 생성 (시뮬레이션)
        function generateSimilarProblem() {
            const patterns = [
                { content: "7, 14, 28, 56, ?", answer: "112", difficulty: "하" },
                { content: "4, 8, 16, 32, ?", answer: "64", difficulty: "하" },
                { content: "6, 12, 24, 48, ?", answer: "96", difficulty: "하" },
                { content: "10, 20, 40, 80, ?", answer: "160", difficulty: "하" }
            ];
            return patterns[Math.floor(Math.random() * patterns.length)];
        }

        // 변형문제 생성 (시뮬레이션)
        function generateVariantProblem() {
            const patterns = [
                { content: "3, 4, 6, 10, 18, ?", answer: "34", difficulty: "상" },
                { content: "1, 1, 2, 3, 5, 8, ?", answer: "13", difficulty: "중" },
                { content: "2, 5, 10, 17, 26, ?", answer: "37", difficulty: "중" },
                { content: "1, 3, 7, 15, 31, ?", answer: "63", difficulty: "상" }
            ];
            return patterns[Math.floor(Math.random() * patterns.length)];
        }

        // 문제 블록 추가
        function addProblemBlock(gridId, problem) {
            const grid = document.getElementById(gridId);
            const block = document.createElement('div');
            block.className = 'problem-block';
            
            // type에 따라 클래스 추가
            if (problem.type === 'modified') {
                block.classList.add('modified');
            } else {
                block.classList.add('similar');
            }
            
            block.setAttribute('data-id', problem.id);
            block.setAttribute('data-question', problem.question);
            block.setAttribute('data-solution', problem.solution);
            block.setAttribute('data-type', problem.type || 'similar');
            // LaTeX 수식을 제거한 텍스트만 추출하여 미리보기
            const previewText = problem.question
                .replace(/\$\$.*?\$\$/g, '[수식]')  // $$ ... $$ 제거
                .replace(/\$.*?\$/g, '[수식]')      // $ ... $ 제거
                .replace(/\\\\\(.*?\\\\\)/g, '[수식]')  // \( ... \) 제거
                .replace(/\\\\\[.*?\\\\\]/g, '[수식]')  // \[ ... \] 제거
                .substring(0, 50);
            
            block.innerHTML = `
                문제 ${problem.number}
                <div class="tooltip">
                    클릭: 선택/해제<br>더블클릭: 상세보기
                </div>
            `;
            
            // 클릭 이벤트 추가
            block.addEventListener('click', function(e) {
                // 일반 클릭: 선택/해제
                this.classList.toggle('selected');
            });
            
            // 더블클릭 이벤트 추가
            block.addEventListener('dblclick', function(e) {
                e.preventDefault();
                console.log('Double click on problem:', problem.id);
                showProblemDetail(problem.id);
            });
            
            // 툴팁은 이미 설정되어 있음
            
            grid.appendChild(block);
        }   
  
        // 시험지 생성 (사용하지 않음 - 시험지 인쇄로 통합)
        /*
        function createExam() {
            const allSelected = document.querySelectorAll('#allProblems .problem-block.selected');
            const similarSelected = [];
            const variantSelected = [];
            
            // type에 따라 분류
            allSelected.forEach(block => {
                const type = block.getAttribute('data-type') || 'similar';
                if (type === 'similar') {
                    similarSelected.push(block);
                } else {
                    variantSelected.push(block);
                } 
            });
            
            if (similarSelected.length === 0 && variantSelected.length === 0) {
                alert('시험지를 출제할 문제를 선택해주세요. (Ctrl + 클릭)');
                return;
            } 
            
            // 선택된 문제들 가져오기
            const similarProblems = Array.from(similarSelected).map(block => ({
                content: block.getAttribute('data-content'),
                answer: block.getAttribute('data-answer')
            }));
            
            const variantProblems = Array.from(variantSelected).map(block => ({
                content: block.getAttribute('data-content'),
                answer: block.getAttribute('data-answer')
            }));
            
            // 시험지 HTML 생성
            const examHTML = `
                <div class="exam-header">
                    <div class="exam-title">수열의 규칙성 평가</div>
                    <div class="exam-info">
                        <div class="student-info">
                            <div>학년: <span>&nbsp;</span></div>
                            <div>반: <span>&nbsp;</span></div>
                            <div>이름: <span>&nbsp;</span></div>
                        </div>
                        <div>날짜: ${new Date().toLocaleDateString('ko-KR')}</div>
                    </div>
                </div>
                
                ${similarProblems.length > 0 ? `
                <div class="exam-section">
                    <div class="section-title">I. 유사문제 (각 10점)</div>
                    ${similarProblems.map((p, i) => `
                        <div class="exam-problem">
                            <div class="problem-number">${i + 1}. 다음 수열의 빈칸에 들어갈 수를 구하시오.</div>
                            <div class="problem-content">${p.content}</div>
                            <div class="answer-space">답:</div>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                
                ${variantProblems.length > 0 ? `
                <div class="exam-section">
                    <div class="section-title">${similarProblems.length > 0 ? 'II' : 'I'}. 변형문제 (각 15점)</div>
                    ${variantProblems.map((p, i) => `
                        <div class="exam-problem">
                            <div class="problem-number">${similarProblems.length + i + 1}. 다음 수열의 규칙을 찾아 뺈칸에 들어갈 수를 구하시오.</div>
                            <div class="problem-content">${p.content}</div>
                            <div class="answer-space">답:</div>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                
                <div style="margin-top: 50px; padding: 20px; background-color: #f0f0f0; border-radius: 8px;">
                    <strong>채점 기준</strong><br>
                    ${similarProblems.length > 0 ? `- 유사문제: 각 10점 (총 ${similarProblems.length * 10}점)<br>` : ''}
                    ${variantProblems.length > 0 ? `- 변형문제: 각 15점 (총 ${variantProblems.length * 15}점)<br>` : ''}
                    - 총점: ${similarProblems.length * 10 + variantProblems.length * 15}점
                </div>
            `;
            
            document.getElementById('examPaper').innerHTML = examHTML;
            document.getElementById('examModal').style.display = 'block';
        }
        */

        // 시험지 인쇄
        function printExam() {
            window.print();
        }
        
        // 선택된 문제 직접 인쇄
        async function printSelectedProblems() {
            const allSelected = document.querySelectorAll('#allProblems .problem-block.selected');
            
            if (allSelected.length === 0) {
                alert('인쇄할 문제를 선택해주세요. (Ctrl + 클릭)');
                return;
            }
            
            // 선택된 문제들의 상세 정보 가져오기
            const selectedProblems = [];
            for (const block of allSelected) {
                const problemId = block.getAttribute('data-id');
                const formData = new FormData();
                formData.append('action', 'get_problem');
                formData.append('id', problemId);
                
                try {
                    const response = await fetch('patternbank_ajax.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    const problem = await response.json();
                    problem.type = block.getAttribute('data-type');
                    selectedProblems.push(problem);
                } catch (e) {
                    console.error('문제 정보 가져오기 실패:', e);
                }
            }
            
            // 시험지 생성
            const examHTML = generateExamHTML(selectedProblems);
            document.getElementById('examPaper').innerHTML = examHTML;
            document.getElementById('examModal').style.display = 'block';
            
            // MathJax 렌더링
            if (window.MathJax) {
                await MathJax.typesetPromise([document.getElementById('examPaper')]);
            }
            
            // 자동 인쇄
            setTimeout(() => {
                window.print();
            }, 500);
        }
        
        // 시험지 HTML 생성 함수
        function generateExamHTML(problems) {
            const similarProblems = problems.filter(p => p.type === 'similar');
            const variantProblems = problems.filter(p => p.type !== 'similar');
            let problemNumber = 1;
            
            return `
                <div style="padding: 10px 0;">
                    ${problems.map((p) => `
                        <div class="exam-problem" style="margin-bottom: 20px; page-break-inside: avoid;">
                            <div style="display: flex; align-items: flex-start;">
                                <div style="font-weight: bold; margin-right: 10px; min-width: 30px;">${problemNumber++}.</div>
                                <div style="flex: 1;">
                                    <div style="margin-bottom: 10px;">${p.question}</div>
                                    ${p.inputanswer ? `
                                        <div style="margin: 10px 0 0 20px;">
                                            ${JSON.parse(p.inputanswer).map(choice => `<div style="margin: 5px 0;">${choice}</div>`).join('')}
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // 모달 표시
        function showModal(title, message) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('modal').style.display = 'block';
        }

        // 모달 닫기
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        // 시험지 모달 닫기
        function closeExamModal() {
            document.getElementById('examModal').style.display = 'none';
        }

        // 모달 외부 클릭 시 닫기
        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            const examModal = document.getElementById('examModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == examModal) {
                examModal.style.display = 'none';
            }
        }

        // JSON 모달 닫기
        function closeJsonModal() {
            document.getElementById('jsonModal').style.display = 'none';
            window.isEditingExisting = false;
            window.editingProblemId = null;
        }
        
        // JSON으로 기존 문제 업데이트
        async function updateProblemFromJson() {
            const jsonInput = document.getElementById('jsonInput').value;
            
            try {
                const data = JSON.parse(jsonInput);
                const question = data.question || data["문제"] || data["문항"];
                const solution = data.solution || data["해설"];
                const choices = data.choices || data["선택지"];
                
                if (!question || !solution) {
                    alert('문제와 해설은 필수 항목입니다.');
                    return;
                }
                
                const formData = new FormData();
                formData.append('action', 'update_problem');
                formData.append('id', window.editingProblemId);
                formData.append('question', question);
                formData.append('solution', solution);
                if (choices) {
                    formData.append('choices', JSON.stringify(choices));
                }
                
                const response = await fetch('patternbank_ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('문제가 JSON으로 성공적으로 업데이트되었습니다.');
                    closeJsonModal();
                    closeProblemDetailModal();
                    location.reload();
                } else {
                    alert('업데이트 중 오류: ' + (result.message || '알 수 없는 오류'));
                }
            } catch (e) {
                console.error('JSON 파싱 오류:', e);
                alert('JSON 형식이 올바르지 않습니다.');
            }
        }

        // 문제 상세 정보 모달 닫기
        function closeProblemDetailModal() {
            document.getElementById('problemDetailModal').style.display = 'none';
        }

        // JSON 문제 저장 (수정된 버전)
        window.saveJsonProblem = async function() {
            // 기존 문제 수정 모드인지 확인
            if (window.isEditingExisting && window.editingProblemId) {
                await updateProblemFromJson();
                return;
            }
            const jsonInput = document.getElementById('jsonInput').value;
            console.log('입력된 JSON:', jsonInput);
            
            try {
                let data;
                
                try {
                    data = JSON.parse(jsonInput);
                    console.log('파싱된 데이터:', data);
                } catch (e) {
                    console.error('JSON 파싱 오류 상세:', e);
                    alert('JSON 파싱 오류:\n' + e.message + '\n\n입력하신 내용을 확인해주세요.');
                    return;
                }
                
                const question = data.question || data["문제"] || data["문항"];
                const solution = data.solution || data["해설"];
                const choices = data.choices || data["선택지"];
                
                console.log('추출된 데이터:', {question, solution, choices});
                
                if (!question || !solution) {
                    alert('문제와 해설은 필수 항목입니다.');
                    return;
                }
                
                const formData = new FormData();
                formData.append('action', 'save_problem');
                formData.append('authorid', PHP_VARS.userid);
                formData.append('cntid', PHP_VARS.cntid);
                formData.append('cnttype', PHP_VARS.cnttype);
                const decodeHtmlEntities = (text) => {
                    const textArea = document.createElement('textarea');
                    textArea.innerHTML = text;
                    return textArea.value;
                };
                
                const convertDollarToLatex = (text) => {
                    text = text.replace(/\$([^$]+)\$/g, '\\($1\\)');
                    text = text.replace(/\$\$([^$]+)\$\$/g, '\\[$1\\]');
                    return text;
                };
                
                let decodedQuestion = decodeHtmlEntities(question);
                let decodedSolution = decodeHtmlEntities(solution);
                
                decodedQuestion = convertDollarToLatex(decodedQuestion);
                decodedSolution = convertDollarToLatex(decodedSolution);
                
                formData.append('question', decodedQuestion);
                formData.append('solution', decodedSolution);
                
                let decodedChoices = null;
                if (choices) {
                    decodedChoices = choices.map(choice => {
                        let decoded = decodeHtmlEntities(choice);
                        return convertDollarToLatex(decoded);
                    });
                    formData.append('choices', JSON.stringify(decodedChoices));
                    formData.append('inputanswer', JSON.stringify(decodedChoices));
                }
                
                formData.append('type', window.currentProblemType || 'similar');
                
                console.log('currentProblemType:', window.currentProblemType);
                console.log('FormData 내용:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ':', value);
                }
                
                try {
                    const response = await fetch('patternbank_ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    console.log('Response status:', response.status);
                    const responseText = await response.text();
                    console.log('Response text:', responseText);
                    
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        console.error('서버 응답 파싱 오류:', e); 
                        alert('서버 응답 오류: ' + responseText);
                        return;
                    }
                    
                    if (result.success) {
                        console.log('Server response:', result);
                        console.log('Type saved to server:', result.type_saved);
                        console.log('Type in database:', result.type_in_db);
                        
                        // 화면에 문제 추가
                        const grid = document.getElementById('allProblems');
                        const problemCount = grid.children.length + 1;
                          
                        addProblemBlock('allProblems', {
                            id: result.id,
                            number: problemCount,
                            question: decodedQuestion,
                            solution: decodedSolution,
                            inputanswer: decodedChoices ? JSON.stringify(decodedChoices) : '',
                            type: window.currentProblemType || 'similar'
                        });
                        
                        closeJsonModal();
                        alert('문제가 성공적으로 추가되었습니다! (Type: ' + (window.currentProblemType || 'similar') + ')');
                        location.reload(); // 페이지 새로고침으로 전체 데이터 다시 로드
                    } else {
                        alert('문제 저장 중 오류: ' + (result.message || '알 수 없는 오류'));
                    }
                    
                } catch (e) {
                    console.error('네트워크 오류:', e);
                    alert('서버와의 통신 중 오류가 발생했습니다.');
                }
                
            } catch (e) {
                console.error('전체 오류:', e);
                alert('오류가 발생했습니다: ' + e.message);
            }
        }

        // 현재 편집 중인 문제 ID 저장
        let currentEditingProblemId = null;
        
        // 문제 상세 정보 표시
        async function showProblemDetail(problemId) {
            console.log('showProblemDetail called with id:', problemId);
            currentEditingProblemId = problemId;
            // 서버에서 문제 정보 가져오기
            const formData = new FormData();
            formData.append('action', 'get_problem');
            formData.append('id', problemId);
            
            try {
                const response = await fetch('patternbank_ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const problem = await response.json();
                
                // 문제 표시 (수식 렌더링)
                document.getElementById('problemQuestion').innerHTML = problem.question;
                
                // 선택지 표시
                if (problem.inputanswer) {
                    let choicesHtml = '<div style="margin-top: 10px;">';
                    const choices = typeof problem.inputanswer === 'string' ? JSON.parse(problem.inputanswer) : problem.inputanswer;
                    choices.forEach((choice, index) => {
                        choicesHtml += `<div style="margin: 8px 0; padding: 8px 12px; background-color: #f0f0f0; border-radius: 5px;">${choice}</div>`;
                    });
                    choicesHtml += '</div>';
                    document.getElementById('problemChoices').innerHTML = choicesHtml;
                } else {
                    document.getElementById('problemChoices').innerHTML = '';
                }
                
                // 해설 표시 (수식 렌더링)
                document.getElementById('problemSolution').innerHTML = problem.solution;
                
                // MathJax로 수식 렌더링
                if (window.MathJax) {
                    MathJax.typesetPromise([document.getElementById('problemQuestion'), document.getElementById('problemChoices'), document.getElementById('problemSolution')]);
                }
                
                // 이미지가 있으면 표시
                if (problem.qstnimgurl) {
                    document.getElementById('problemQuestionImage').innerHTML = `<img src="${problem.qstnimgurl}" style="max-width: 100%; height: auto;">`;
                }
                if (problem.solimgurl) {
                    document.getElementById('problemSolutionImage').innerHTML = `<img src="${problem.solimgurl}" style="max-width: 100%; height: auto;">`;
                }
                
                document.getElementById('problemDetailModal').style.display = 'block';
                
            } catch (e) {
                console.error('문제 정보를 가져오는 중 오류 발생:', e);
            }
        }

        // 문제 변경 사항 저장
        async function saveProblemChanges() {
            if (!currentEditingProblemId) {
                alert('편집 중인 문제 ID가 없습니다.');
                return;
            }
            
            console.log('Saving problem ID:', currentEditingProblemId);
            
            const question = document.getElementById('problemQuestion').innerText.trim();
            const solution = document.getElementById('problemSolution').innerText.trim();
            const choicesDiv = document.getElementById('problemChoices');
            
            // 선택지 처리
            let choices = null;
            if (choicesDiv.innerText.trim()) {
                choices = choicesDiv.innerText.split('\n').filter(line => line.trim());
            }
            
            console.log('Save data:', {
                id: currentEditingProblemId,
                question: question.substring(0, 50) + '...',
                solution: solution.substring(0, 50) + '...',
                choices: choices
            });
            
            const formData = new FormData();
            formData.append('action', 'update_problem');
            formData.append('id', currentEditingProblemId);
            formData.append('question', question);
            formData.append('solution', solution);
            if (choices && choices.length > 0) {
                formData.append('choices', JSON.stringify(choices));
            }
            
            try {
                const response = await fetch('patternbank_ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    alert('서버 응답 오류: ' + responseText.substring(0, 200));
                    return;
                }
                
                if (result.success) {
                    alert('문제가 성공적으로 수정되었습니다.');
                    closeProblemDetailModal();
                    location.reload();
                } else {
                    alert('수정 중 오류 발생: ' + (result.message || result.error || '알 수 없는 오류'));
                }
            } catch (e) {
                console.error('수정 중 오류:', e);
                alert('서버와의 통신 중 오류가 발생했습니다.');
            }
        }
        
        // JSON 편집기 표시
        function showJsonEditor() {
            if (!currentEditingProblemId) return;
            
            const question = document.getElementById('problemQuestion').innerText;
            const solution = document.getElementById('problemSolution').innerText;
            const choicesDiv = document.getElementById('problemChoices');
            
            let choices = null;
            if (choicesDiv.innerText.trim()) {
                choices = choicesDiv.innerText.split('\n').filter(line => line.trim());
            }
            
            const jsonData = {
                "문항": question,
                "선택지": choices,
                "해설": solution
            };
            
            document.getElementById('jsonInput').value = JSON.stringify(jsonData, null, 2);
            document.getElementById('jsonModal').style.display = 'block';
            window.isEditingExisting = true;
            window.editingProblemId = currentEditingProblemId;
        }

        // ESC 키로 모달 닫기
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeExamModal();
                closeJsonModal();
                closeProblemDetailModal();
            }
        });
        
        // 유형 분석 편집 기능
        let analysisEditTimeout = null;
        let originalAnalysisText = '';
        
        function enableAnalysisEdit() {
            const analysisDiv = document.getElementById('analysisText');
            originalAnalysisText = analysisDiv.innerHTML;
            
            analysisDiv.contentEditable = true;
            analysisDiv.focus();
            
            // 텍스트 전체 선택
            const range = document.createRange();
            range.selectNodeContents(analysisDiv);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // blur 이벤트로 저장
            analysisDiv.onblur = function() {
                saveAnalysis();
            };
            
            // Enter 키로 줄바꿈, Ctrl+Enter로 저장
            analysisDiv.onkeydown = function(e) {
                if (e.key === 'Enter' && e.ctrlKey) {
                    e.preventDefault();
                    analysisDiv.blur();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    analysisDiv.innerHTML = originalAnalysisText;
                    analysisDiv.blur();
                }
            };
        }
        
        async function saveAnalysis() {
            const analysisDiv = document.getElementById('analysisText');
            analysisDiv.contentEditable = false;
            
            const newText = analysisDiv.innerHTML;
            if (newText === originalAnalysisText) {
                return; // 변경사항 없음
            }
            
            // 저장 중 표시
            let indicator = analysisDiv.querySelector('.analysis-save-indicator');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.className = 'analysis-save-indicator';
                analysisDiv.style.position = 'relative';
                analysisDiv.appendChild(indicator);
            }
            indicator.textContent = '저장 중...';
            indicator.style.display = 'block';
            
            const formData = new FormData();
            formData.append('action', 'save_analysis');
            formData.append('cntid', PHP_VARS.cntid);
            formData.append('analysis', newText);
            
            console.log('Saving analysis:', {
                cntid: PHP_VARS.cntid,
                textLength: newText.length
            });
            
            try {
                const response = await fetch('patternbank_ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid server response');
                }
                
                if (result.success) {
                    indicator.textContent = '✓ 저장됨';
                    originalAnalysisText = newText; // 성공 시 원본 텍스트 업데이트
                    setTimeout(() => {
                        indicator.style.display = 'none';
                    }, 2000);
                } else {
                    indicator.style.display = 'none';
                    alert('저장 중 오류 발생: ' + (result.message || '알 수 없는 오류'));
                    analysisDiv.innerHTML = originalAnalysisText;
                }
            } catch (e) {
                console.error('저장 중 오류:', e);
                indicator.style.display = 'none';
                alert('서버와의 통신 중 오류가 발생했습니다.');
                analysisDiv.innerHTML = originalAnalysisText;
            }
        }

        // 해설지 인쇄 함수
        async function printSolutionSheet() {
            const allSelected = document.querySelectorAll('#allProblems .problem-block.selected');
            
            if (allSelected.length === 0) {
                alert('해설지를 인쇄할 문제를 선택해주세요. (Ctrl + 클릭)');
                return;
            } 
            
            // 선택된 문제들의 상세 정보 가져오기  
            const selectedProblems = [];
            for (const block of allSelected) {
                const problemId = block.getAttribute('data-id');
                const formData = new FormData();
                formData.append('action', 'get_problem');
                formData.append('id', problemId);
                
                try {
                    const response = await fetch('patternbank_ajax.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    const problem = await response.json();
                    problem.type = block.getAttribute('data-type'); 
                    selectedProblems.push(problem);
                } catch (e) {
                    console.error('문제 정보 가져오기 실패:', e);
                }
            }
            
            // 해설지 HTML 생성
            const solutionHTML = generateSolutionHTML(selectedProblems);
            document.getElementById('examPaper').innerHTML = solutionHTML;
            document.getElementById('examModal').style.display = 'block';
            
            // MathJax 렌더링
            if (window.MathJax) {
                await MathJax.typesetPromise([document.getElementById('examPaper')]);
            }
            
            // 자동 인쇄
            setTimeout(() => {
                window.print();
            }, 500);
        }
        
        // 해설지 HTML 생성 함수
        function generateSolutionHTML(problems) {
            let problemNumber = 1;
            return `
                <div style="padding: 10px 0;">
                    <h2 style="text-align: center; margin-bottom: 30px; font-size: 24px; font-weight: bold;">해설지</h2>
                    ${problems.map((p) => `
                        <div style="margin-bottom: 20px; page-break-inside: avoid;">
                            <div style="font-weight: bold; margin-bottom: 10px;">${problemNumber++}.</div>
                            <div style="margin-left: 20px; line-height: 1.8;">
                                ${p.solution}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        // 콘솔에 입력하여 JSON 파싱 테스트
        const testJson = `{
          "문항": "x에 대한 삼차방정식 $x^3+(a+2)x^2+3ax+a^2=0$이 중근을 갖도록 하는 실수 $a$의 값을 모두 구하여라.",
          "선택지": [
            "① $a=0$",
            "② $a=1$",
            "③ $a=0$ 또는 $a=1$",
            "④ 해당 조건을 만족하는 실수 $a$는 존재하지 않는다",
            "⑤ 모든 실수 $a$"
          ],
          "해설": "함수를 $f(x)=x^3+(a+2)x^2+3ax+a^2$라 두면\\n$f(-a)=(-a)^3+(a+2)(-a)^2+3a(-a)+a^2=-a^3+(a+2)a^2-3a^2+a^2=0$이므로 $f(x)=(x+a)(x^2+2x+a)$로 인수분해된다.\\n삼차방정식 $f(x)=0$이 중근을 가지려면 다음 두 경우 가운데 하나가 성립해야 한다.\\n(i) $x=-a$가 이차방정식 $x^2+2x+a=0$의 근일 때\\n$(-a)^2+2(-a)+a=a^2-2a+a=a^2-a=a(a-1)=0$\\n따라서 $a=0$ 또는 $a=1$.\\n(ii) 이차방정식 $x^2+2x+a=0$이 중근을 가질 때\\n판별식 $D=2^2-4a=4-4a=0$에서 $a=1$.\\n(i), (ii)를 종합하면 중근을 갖도록 하는 실수 $a$는 $a=0$ 또는 $a=1$이다.\\n따라서 정답은 ③이다."
        }`;

        try {
            const parsed = JSON.parse(testJson);
            console.log('JSON 파싱 성공:', parsed);
        } catch (e) {
            console.error('JSON 파싱 실패:', e);
        }
    </script>
</body>
</html>