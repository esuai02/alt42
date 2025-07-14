<?php 
include_once("/home/moodle/public_html/moodle/config.php"); 
global $DB, $USER;
require_login();
$studentid = $_GET["userid"];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>문제풀이 지원 시스템</title>
    <!-- MathJax for mathematical notation -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']],
                processEscapes: true,
                processEnvironments: true
            },
            options: {
                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre']
            },
            startup: {
                ready() {
                    console.log('MathJax is loaded and ready');
                    MathJax.startup.defaultReady();
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .status-bar {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-item label {
            font-weight: 500;
            color: #666;
        }

        .status-item span {
            color: #3498db;
            font-weight: bold;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .panel h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .upload-area:hover {
            border-color: #3498db;
            background-color: #f8f9fa;
        }

        .upload-area.dragover {
            border-color: #3498db;
            background-color: #e3f2fd;
        }

        .upload-icon {
            font-size: 48px;
            color: #3498db;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #666;
            margin-bottom: 10px;
        }

        .upload-input {
            display: none;
        }

        .image-preview {
            max-width: 100%;
            margin-top: 15px;
            border-radius: 5px;
            display: none;
        }

        .solution-content {
            min-height: 200px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            white-space: pre-wrap;
            overflow-y: auto;
            max-height: 400px;
            line-height: 1.8;
            font-size: 14px;
        }

        .solution-content h3 {
            color: #2c3e50;
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .solution-content ul, .solution-content ol {
            margin-left: 20px;
            margin-bottom: 10px;
        }

        .solution-content li {
            margin-bottom: 5px;
        }

        .answer-box {
            background-color: #e8f5e9;
            border: 2px solid #4caf50;
            border-radius: 5px;
            padding: 10px 15px;
            margin: 10px 0;
            font-weight: bold;
            color: #2e7d32;
            display: block;
            clear: both;
        }

        .solution-content h3 {
            background-color: #e3f2fd;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 15px 0 10px 0;
            color: #1565c0;
            font-size: 16px;
        }

        .solution-content strong {
            color: #1976d2;
        }

        .narration-content {
            margin-top: 15px;
            padding: 15px;
            background-color: #f3e5f5;
            border-radius: 5px;
            border: 1px solid #ba68c8;
        }

        .narration-content h3 {
            font-size: 16px;
            color: #7b1fa2;
            margin-bottom: 10px;
        }

        #narrationText {
            color: #4a148c;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .audio-control-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .audio-control-btn:hover {
            transform: scale(1.1);
        }

        .audio-control-btn svg {
            width: 24px;
            height: 24px;
            fill: #3498db;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background-color: #229954;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .history-panel {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .history-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .history-item:hover {
            background-color: #f8f9fa;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-date {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }

        .history-title {
            font-weight: 500;
            color: #2c3e50;
        }

        .history-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 10px;
        }

        .type-exam {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .type-school {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .type-mathking {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .type-textbook {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .audio-player {
            margin-top: 15px;
            display: none;
        }

        .audio-player audio {
            width: 100%;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            font-size: 14px;
            cursor: pointer;
        }

        select:focus {
            outline: none;
            border-color: #3498db;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 50px auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        .modal-close:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>문제풀이 지원 시스템</h1>
            <div class="status-bar">
                <div class="status-item">
                    <label>학생:</label>
                    <span id="studentName">학생 <?php echo $studentid; ?></span>
                </div>
                <div class="status-item">
                    <label>문제 유형:</label>
                    <select id="problemType">
                        <option value="">선택하세요</option>
                        <option value="exam">내신 기출</option>
                        <option value="school">학교 프린트</option>
                        <option value="mathking">MathKing 문제</option>
                        <option value="textbook">시중교재</option>
                    </select>
                </div>
                <div class="status-item">
                    <label>처리 상태:</label>
                    <span id="processStatus">대기중</span>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="panel">
                <h2>문제 업로드</h2>
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">문제 이미지를 드래그하거나 클릭하여 업로드</div>
                    <input type="file" id="fileInput" class="upload-input" accept="image/*">
                    <img id="imagePreview" class="image-preview" alt="문제 미리보기">
                </div>
                <div class="action-buttons">
                    <button id="checkAnswerBtn" class="btn btn-primary" disabled>
                        정답 체크
                    </button>
                    <button id="clearBtn" class="btn btn-secondary">
                        초기화
                    </button>
                </div>
            </div>

            <div class="panel">
                <h2>
                    AI 해설
                    <button id="playAudioBtn" class="audio-control-btn" style="display: none;" title="음성 재생">
                        <svg viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <button id="pauseAudioBtn" class="audio-control-btn" style="display: none;" title="일시정지">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>
                </h2>
                <div class="loading" id="solutionLoading">
                    <div class="spinner"></div>
                    <p>AI가 문제를 분석중입니다...</p>
                </div>
                <div class="solution-content" id="solutionContent">
                    해설이 여기에 표시됩니다.
                </div>
                <div class="audio-player" id="audioPlayer">
                    <audio controls id="audioElement"></audio>
                </div>
                <div class="action-buttons">
                    <button id="generateNarrationBtn" class="btn btn-primary" disabled>
                        설명 나레이션 생성
                    </button>
                    <button id="generateTTSBtn" class="btn btn-primary" disabled>
                        음성 생성
                    </button>
                    <button id="sendMessageBtn" class="btn btn-success" disabled>
                        학생에게 전송
                    </button>
                    <button id="saveContentBtn" class="btn btn-secondary" disabled>
                        컨텐츠 저장
                    </button>
                </div>
                <div class="narration-content" id="narrationContent" style="display: none;">
                    <h3>나레이션 스크립트</h3>
                    <div id="narrationText"></div>
                </div>
            </div>
        </div>

        <div class="history-panel">
            <h2>최근 문제 해설 기록</h2>
            <div id="historyList">
                <!-- 동적으로 생성됨 -->
            </div>
        </div>
    </div>

    <!-- 메시지 전송 모달 -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" id="modalClose">&times;</span>
            <h3>학생에게 메시지 전송</h3>
            <textarea id="messageText" style="width: 100%; height: 100px; margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="추가 메시지를 입력하세요..."></textarea>
            <div class="action-buttons">
                <button id="confirmSendBtn" class="btn btn-success">전송</button>
                <button id="cancelSendBtn" class="btn btn-secondary">취소</button>
            </div>
        </div>
    </div>

    <script>
        // 전역 변수
        let uploadedFile = null;
        let currentSolution = '';
        let currentAudioUrl = '';
        let currentNarration = '';
        let audioElement = null;

        // DOM 요소
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const imagePreview = document.getElementById('imagePreview');
        const checkAnswerBtn = document.getElementById('checkAnswerBtn');
        const clearBtn = document.getElementById('clearBtn');
        const solutionContent = document.getElementById('solutionContent');
        const solutionLoading = document.getElementById('solutionLoading');
        const generateNarrationBtn = document.getElementById('generateNarrationBtn');
        const generateTTSBtn = document.getElementById('generateTTSBtn');
        const sendMessageBtn = document.getElementById('sendMessageBtn');
        const saveContentBtn = document.getElementById('saveContentBtn');
        const audioPlayer = document.getElementById('audioPlayer');
        const audioElementPlayer = document.getElementById('audioElement');
        const narrationContent = document.getElementById('narrationContent');
        const narrationText = document.getElementById('narrationText');
        const playAudioBtn = document.getElementById('playAudioBtn');
        const pauseAudioBtn = document.getElementById('pauseAudioBtn');
        const problemType = document.getElementById('problemType');
        const processStatus = document.getElementById('processStatus');
        const historyList = document.getElementById('historyList');
        const messageModal = document.getElementById('messageModal');
        const modalClose = document.getElementById('modalClose');
        const messageText = document.getElementById('messageText');
        const confirmSendBtn = document.getElementById('confirmSendBtn');
        const cancelSendBtn = document.getElementById('cancelSendBtn');

        // 수식 포맷팅 함수
        function formatMathContent(content) {
            // 줄 단위로 처리
            const lines = content.split('\n');
            const formattedLines = lines.map(line => {
                // 섹션 헤더 처리 (대괄호로 둘러싸인 텍스트)
                if (line.match(/^\[.*\]$/)) {
                    return '<h3>' + line.substring(1, line.length - 1) + '</h3>';
                }
                
                // 번호 목록 처리
                line = line.replace(/^(\d+)\.\s/, '<strong>$1.</strong> ');
                
                // 답 강조 처리
                if (line.startsWith('답:')) {
                    return '<div class="answer-box">' + line + '</div>';
                }
                
                // 리스트 아이템 처리
                line = line.replace(/^-\s/, '• ');
                
                return line;
            });
            
            // 줄바꿈으로 다시 결합
            return formattedLines.join('<br>');
        }

        // 파일 업로드 처리
        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileUpload(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileUpload(e.target.files[0]);
            }
        });

        function handleFileUpload(file) {
            if (!file.type.startsWith('image/')) {
                alert('이미지 파일만 업로드 가능합니다.');
                return;
            }

            uploadedFile = file;
            const reader = new FileReader();
            
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                checkAnswerBtn.disabled = false;
                processStatus.textContent = '업로드 완료';
            };
            
            reader.readAsDataURL(file);
        }

        // 정답 체크
        checkAnswerBtn.addEventListener('click', async () => {
            if (!uploadedFile) return;

            if (!problemType.value) {
                alert('문제 유형을 선택해주세요.');
                return;
            }

            checkAnswerBtn.disabled = true;
            solutionLoading.classList.add('active');
            solutionContent.textContent = '';
            processStatus.textContent = '분석중...';

            try {
                // FormData 생성
                const formData = new FormData();
                formData.append('image', uploadedFile);
                formData.append('problemType', problemType.value);
                formData.append('studentId', '<?php echo $studentid; ?>');

                // OpenAI API를 통한 문제 분석
                const response = await fetch('analyze_problem.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentSolution = data.solution;
                    console.log('Received solution:', currentSolution); // 디버깅용
                    // HTML로 표시하여 수식이 제대로 렌더링되도록 함
                    solutionContent.innerHTML = formatMathContent(currentSolution);
                    console.log('Formatted HTML:', solutionContent.innerHTML); // 디버깅용
                    // MathJax에게 새로운 수식을 렌더링하도록 지시
                    setTimeout(() => {
                        if (window.MathJax && window.MathJax.typesetPromise) {
                            window.MathJax.typesetPromise([solutionContent])
                                .then(() => {
                                    console.log('MathJax rendering completed');
                                })
                                .catch((e) => {
                                    console.error('MathJax rendering error:', e);
                                });
                        }
                    }, 100);
                    generateNarrationBtn.disabled = false;
                    saveContentBtn.disabled = false;
                    processStatus.textContent = '분석 완료';
                } else {
                    throw new Error(data.error || '분석 실패');
                }

            } catch (error) {
                console.error('Error:', error);
                alert('문제 분석 중 오류가 발생했습니다: ' + error.message);
                processStatus.textContent = '오류 발생';
            } finally {
                solutionLoading.classList.remove('active');
                checkAnswerBtn.disabled = false;
            }
        });

        // 나레이션 생성
        generateNarrationBtn.addEventListener('click', async () => {
            if (!currentSolution) return;

            generateNarrationBtn.disabled = true;
            processStatus.textContent = '나레이션 생성중...';

            try {
                const response = await fetch('generate_narration.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        solution: currentSolution
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    currentNarration = data.narration;
                    narrationText.textContent = currentNarration;
                    narrationContent.style.display = 'block';
                    generateTTSBtn.disabled = false;
                    processStatus.textContent = '나레이션 생성 완료';
                } else {
                    throw new Error(data.error || '나레이션 생성 실패');
                }

            } catch (error) {
                console.error('Error:', error);
                alert('나레이션 생성 중 오류가 발생했습니다: ' + error.message);
                processStatus.textContent = '오류 발생';
            } finally {
                generateNarrationBtn.disabled = false;
            }
        });

        // TTS 생성
        generateTTSBtn.addEventListener('click', async () => {
            if (!currentNarration) {
                alert('먼저 나레이션을 생성해주세요.');
                return;
            }

            generateTTSBtn.disabled = true;
            processStatus.textContent = '음성 생성중...';

            try {
                const response = await fetch('generate_tts.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        text: currentNarration,
                        voice: 'nova' // 여성 목소리, 다른 옵션: alloy, echo, fable, onyx, shimmer
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    currentAudioUrl = data.audioUrl;
                    
                    // 숨겨진 오디오 엘리먼트 생성
                    if (!audioElement) {
                        audioElement = new Audio();
                    }
                    audioElement.src = currentAudioUrl;
                    
                    // 재생 버튼 표시
                    playAudioBtn.style.display = 'inline-flex';
                    sendMessageBtn.disabled = false;
                    processStatus.textContent = '음성 생성 완료';
                } else {
                    throw new Error(data.error || '음성 생성 실패');
                }

            } catch (error) {
                console.error('Error:', error);
                alert('음성 생성 중 오류가 발생했습니다: ' + error.message);
                processStatus.textContent = '오류 발생';
            } finally {
                generateTTSBtn.disabled = false;
            }
        });

        // 메시지 전송
        sendMessageBtn.addEventListener('click', () => {
            messageModal.style.display = 'block';
            messageText.value = '';
        });

        modalClose.addEventListener('click', () => {
            messageModal.style.display = 'none';
        });

        cancelSendBtn.addEventListener('click', () => {
            messageModal.style.display = 'none';
        });

        confirmSendBtn.addEventListener('click', async () => {
            const additionalMessage = messageText.value;
            
            try {
                // API 호출 (실제 구현시 백엔드 엔드포인트로 변경)
                // const response = await fetch('/api/send-message', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json'
                //     },
                //     body: JSON.stringify({
                //         studentId: '<?php echo $studentid; ?>',
                //         solution: currentSolution,
                //         audioUrl: currentAudioUrl,
                //         additionalMessage: additionalMessage
                //     })
                // });

                alert('메시지가 성공적으로 전송되었습니다.');
                messageModal.style.display = 'none';
                processStatus.textContent = '전송 완료';
                
                // 히스토리에 추가
                addToHistory();
                
            } catch (error) {
                console.error('Error:', error);
                alert('메시지 전송 중 오류가 발생했습니다.');
            }
        });

        // 컨텐츠 저장
        saveContentBtn.addEventListener('click', async () => {
            if (!currentSolution) return;

            try {
                // API 호출 (실제 구현시 백엔드 엔드포인트로 변경)
                // const response = await fetch('/api/save-content', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json'
                //     },
                //     body: JSON.stringify({
                //         studentId: '<?php echo $studentid; ?>',
                //         problemType: problemType.value,
                //         solution: currentSolution,
                //         audioUrl: currentAudioUrl
                //     })
                // });

                alert('컨텐츠가 저장되었습니다.');
                processStatus.textContent = '저장 완료';
                
            } catch (error) {
                console.error('Error:', error);
                alert('컨텐츠 저장 중 오류가 발생했습니다.');
            }
        });

        // 초기화
        clearBtn.addEventListener('click', () => {
            uploadedFile = null;
            imagePreview.src = '';
            imagePreview.style.display = 'none';
            solutionContent.innerHTML = '해설이 여기에 표시됩니다.';
            audioPlayer.style.display = 'none';
            audioElementPlayer.src = '';
            checkAnswerBtn.disabled = true;
            generateNarrationBtn.disabled = true;
            generateTTSBtn.disabled = true;
            sendMessageBtn.disabled = true;
            saveContentBtn.disabled = true;
            problemType.value = '';
            processStatus.textContent = '대기중';
            currentSolution = '';
            currentNarration = '';
            currentAudioUrl = '';
            narrationContent.style.display = 'none';
            narrationText.textContent = '';
            playAudioBtn.style.display = 'none';
            pauseAudioBtn.style.display = 'none';
            if (audioElement) {
                audioElement.pause();
                audioElement.src = '';
            }
        });

        // 히스토리 추가
        function addToHistory() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('ko-KR');
            const timeStr = now.toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });
            
            const historyItem = document.createElement('div');
            historyItem.className = 'history-item';
            historyItem.innerHTML = `
                <div class="history-date">${dateStr} ${timeStr}</div>
                <div class="history-title">
                    문제 해설
                    <span class="history-type type-${problemType.value}">${problemType.options[problemType.selectedIndex].text}</span>
                </div>
            `;
            
            historyList.insertBefore(historyItem, historyList.firstChild);
            
            // 최대 10개까지만 표시
            while (historyList.children.length > 10) {
                historyList.removeChild(historyList.lastChild);
            }
        }

        // 페이지 로드시 초기화
        window.addEventListener('load', () => {
            // 히스토리 로드 (실제 구현시 API 호출)
            // loadHistory();
        });

        // 오디오 재생 컨트롤
        playAudioBtn.addEventListener('click', () => {
            if (audioElement && audioElement.src) {
                audioElement.play();
                playAudioBtn.style.display = 'none';
                pauseAudioBtn.style.display = 'inline-flex';
            }
        });

        pauseAudioBtn.addEventListener('click', () => {
            if (audioElement) {
                audioElement.pause();
                pauseAudioBtn.style.display = 'none';
                playAudioBtn.style.display = 'inline-flex';
            }
        });

        // 오디오 종료 시 버튼 상태 변경
        if (audioElement) {
            audioElement.addEventListener('ended', () => {
                pauseAudioBtn.style.display = 'none';
                playAudioBtn.style.display = 'inline-flex';
            });
        }

        // 모달 외부 클릭시 닫기
        window.addEventListener('click', (e) => {
            if (e.target === messageModal) {
                messageModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>