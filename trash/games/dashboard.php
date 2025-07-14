<?php
// 에러 표시 설정 (개발 단계에서만 사용)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Moodle 환경 설정 포함
require_once("/home/moodle/public_html/moodle/config.php");
global $DB, $USER;

// GET 파라미터 받기
$cid = isset($_GET["cid"]) ? intval($_GET["cid"]) : 0;
$studentid = isset($_GET["studentid"]) ? intval($_GET["studentid"]) : $USER->id;
$subjectname = isset($_GET["title"]) ? $_GET["title"] : '';

$userrole = $DB->get_record_sql("SELECT data AS role FROM mdl_user_info_data WHERE userid = ? AND fieldid = ?", array($USER->id, 22));
$role = $userrole->role;

if ($role !== 'student') {
    $gametools = 'https://chatgpt.com/g/g-675fbd348b148191b39451411ac63f80';
}

// 게임 결과 저장 처리 (AJAX 요청)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'save_game_result') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (isset($data['action']) && $data['action'] === 'save_game_result') {
            $score = intval($data['score']);
            $stage = intval($data['stage']);
            $time = intval($data['time']);
            $user_id = $USER->id;

            // 게임 결과 삽입
            $gameResult = new stdClass();
            $gameResult->user_id = $user_id;
            $gameResult->score = $score;
            $gameResult->stage = $stage;
            $gameResult->time = $time;
            $gameResult->played_at = time();
            $DB->insert_record('game_results', $gameResult);

            echo json_encode(['success' => true]);
            exit();
        }
    } else if ($action === 'save_php_code') {
        $file_input = isset($_POST['file']) ? $_POST['file'] : '';
        $savefile_input = isset($_POST['savefile']) ? $_POST['savefile'] : ''; // 사용하지 않음
        $game_id = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;
    
        if ($game_id > 0) {
            // 같은 폴더 내에 game_{game_id}.php 파일로 생성
            $filename = __DIR__ . '/game_' . $game_id . '.php';
    
            if (file_put_contents($filename, $file_input) !== false) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => '파일 생성에 실패했습니다.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '게임ID가 유효하지 않습니다.']);
        }
        exit();
    }
}

// 단원 정보 가져오기
$curri = $DB->get_record_sql("SELECT * FROM mdl_abessi_curriculum WHERE id = ?", array($cid));

// 새로운 게임 추가 처리
if (isset($_POST['add_game']) && $role !== 'student') {
    $newGame = new stdClass();
    $newGame->name = $_POST['game_name'];
    $newGame->subject_name = $subjectname;
    $newGame->unit_name = $curri->unit_name; // 실제 단원명 필요
    $newGame->category = $_POST['category'];
    $newGame->icon = $_POST['icon'];
    $newGame->difficulty = $_POST['difficulty'];
    $newGame->created_at = time();
    $newGame->updated_at = time();
    $DB->insert_record('alt42_games_info', $newGame);

    header("Location: " . $_SERVER['PHP_SELF'] . "?cid={$cid}&title=" . urlencode($subjectname));
    exit();
}

// 게임 삭제 처리
if (isset($_POST['delete_game']) && $role !== 'student') {
    $deleteId = intval($_POST['delete_game_id']);
    $DB->delete_records('alt42_games_info', array('id' => $deleteId));

    header("Location: " . $_SERVER['PHP_SELF'] . "?cid={$cid}&title=" . urlencode($subjectname));
    exit();
}

// 게임 데이터 가져오기
$gamesData = $DB->get_records('alt42_games_info', array('subject_name' => $subjectname, 'unit_name' => $curri->unit_name));

// 게임 데이터 분류
$unitGames = array(
    'all' => array(),
    'formula' => array(),
    'application' => array(),
    'concept' => array()
);

foreach ($gamesData as $game) {
    $userRecord = $DB->get_record('alt42_games_user_records', array('game_id' => $game->id, 'user_id' => $studentid));

    $gameInfo = array(
        'id' => $game->id,
        'name' => $game->name,
        'category' => $game->category,
        'icon' => $game->icon,
        'difficulty' => $game->difficulty,
        'myRank' => isset($userRecord->rank) ? $userRecord->rank : null,
        'totalPlayers' => $DB->count_records('alt42_games_user_records', array('game_id' => $game->id)),
        'lastPlayed' => isset($userRecord->last_played) ? date('Y-m-d', $userRecord->last_played) : null,
        'score' => isset($userRecord->score) ? $userRecord->score : 0,
        'file' => isset($game->file) ? $game->file : '',
        'savefile' => isset($game->savefile) ? $game->savefile : ''
    );

    $unitGames['all'][] = $gameInfo;
    $unitGames[$game->category][] = $gameInfo;
}

// 추천 학습 데이터 (예시)
$recommendedGames = array();

// 단원 전체 랭킹 데이터
$unitRankingsData = $DB->get_records('alt42_games_unit_rankings', array('unit_name' => $curri->unit_name, 'subject_name' => $subjectname), 'rank ASC', '*', 0, 10);

$unitRankings = array();
foreach ($unitRankingsData as $ranking) {
    $unitRankings[] = array(
        'rank' => $ranking->rank,
        'name' => $ranking->user_name,
        'score' => $ranking->score,
        'avatar' => $ranking->user_avatar
    );
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Math Games</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="p-4 max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($subjectname); ?></h1>
            <div class="h-1 w-24 bg-blue-500"></div>
        </div>
        <?php if ($role !== 'student'): ?>
            <table>
                <tr>
                    <td>
                        <button>
                            <a style="color:white;font-size:1.5rem;" href="<?php echo $gametools; ?>" target="_blank">🤖</a>
                        </button>
                    </td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>
                        <button id="toggleFormButton" class="px-4 py-2 bg-green-500 text-white rounded">
                            새로운 게임 등록
                        </button>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </div>

    <?php if ($role !== 'student'): ?>
        <div id="newGameForm" class="mb-6 bg-gray-100 p-4 rounded hidden">
            <h2 class="text-xl font-bold mb-2">새로운 게임 등록</h2>
            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">게임 이름</label>
                    <input type="text" name="game_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">카테고리</label>
                    <select name="category" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="formula">공식</option>
                        <option value="application">공식적용</option>
                        <option value="concept">개념성찰</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">아이콘</label>
                    <input type="text" name="icon" required placeholder="예: 🎯" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">난이도</label>
                    <select name="difficulty" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="초급">초급</option>
                        <option value="중급">중급</option>
                        <option value="고급">고급</option>
                    </select>
                </div>
                <button type="submit" name="add_game" class="px-4 py-2 bg-blue-500 text-white rounded">게임 추가</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3">
            <div>
                <div class="flex space-x-4 mb-4">
                    <button class="tab-button px-4 py-2 bg-blue-500 text-white rounded" data-tab="all">
                        <svg class="w-4 h-4 inline-block mr-2" data-lucide="book-open"></svg>전체
                    </button>
                    <button class="tab-button px-4 py-2 bg-gray-200 rounded" data-tab="formula">
                        <svg class="w-4 h-4 inline-block mr-2" data-lucide="calculator"></svg>공식
                    </button>
                    <button class="tab-button px-4 py-2 bg-gray-200 rounded" data-tab="application">
                        <svg class="w-4 h-4 inline-block mr-2" data-lucide="target"></svg>공식적용
                    </button>
                    <button class="tab-button px-4 py-2 bg-gray-200 rounded" data-tab="concept">
                        <svg class="w-4 h-4 inline-block mr-2" data-lucide="brain-cog"></svg>개념성찰
                    </button>
                </div>

                <?php foreach ($unitGames as $category => $games): ?>
                    <div class="tab-content <?php echo $category !== 'all' ? 'hidden' : ''; ?>" id="<?php echo $category; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($games as $game): ?>
                                <div class="border rounded hover:shadow-lg transition-shadow">
                                    <div class="flex flex-row items-center space-x-4 p-4">
                                        <div class="text-3xl"><?php echo htmlspecialchars($game['icon']); ?></div>
                                        <div>
                                            <h2 class="text-lg font-bold"><?php echo htmlspecialchars($game['name']); ?></h2>
                                            <?php
                                            $badgeClass = '';
                                            if ($game['difficulty'] === '초급') {
                                                $badgeClass = 'bg-green-500 text-white';
                                            } elseif ($game['difficulty'] === '중급') {
                                                $badgeClass = 'bg-blue-500 text-white';
                                            } else {
                                                $badgeClass = 'bg-red-500 text-white';
                                            }
                                            ?>
                                            <span class="px-2 py-1 text-sm rounded <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($game['difficulty']); ?></span>
                                        </div>
                                    </div>
                                    <div class="px-4 pb-4">
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-500">진행도</span>
                                                <div class="flex items-center">
                                                    <div class="w-32 h-2 bg-gray-200 rounded-full mr-2">
                                                        <div 
                                                            class="h-full bg-blue-500 rounded-full"
                                                            style="width: <?php echo intval($game['score']); ?>%;"
                                                        ></div>
                                                    </div>
                                                    <span class="text-sm"><?php echo intval($game['score']); ?>%</span>
                                                </div>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-500">랭킹</span>
                                                <span class="font-medium"><?php echo isset($game['myRank']) ? intval($game['myRank']) : '-'; ?>/<?php echo intval($game['totalPlayers']); ?></span>
                                            </div>

                                            <?php if ($role !== 'student'): ?>
                                                <div class="flex space-x-2 mt-2">
                                                    <button class="px-3 py-1 bg-purple-500 text-white rounded play-button" data-game-id="<?php echo intval($game['id']); ?>">
                                                        플레이
                                                    </button>
                                                    <button 
                                                        class="px-3 py-1 bg-blue-500 text-white rounded code-input-button" 
                                                        data-game-id="<?php echo intval($game['id']); ?>"
                                                        data-game-file="<?php echo htmlspecialchars($game['file']); ?>"
                                                        data-game-savefile="<?php echo htmlspecialchars($game['savefile']); ?>"
                                                    >
                                                       파일입력
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($games)): ?>
                                <p class="text-gray-500">등록된 게임이 없습니다.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="border rounded">
                <div class="p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" data-lucide="thumbs-up"></svg>
                        추천 학습
                    </h2>
                </div>
                <div class="px-4 pb-4">
                    <div class="space-y-4">
                        <?php if (empty($recommendedGames)): ?>
                            <p class="text-gray-500">추천 학습이 없습니다.</p>
                        <?php else: ?>
                            <?php foreach ($recommendedGames as $rgame): ?>
                                <div class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors">
                                    <div class="font-medium mb-1"><?php echo htmlspecialchars($rgame['name']); ?></div>
                                    <div class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($rgame['description']); ?></div>
                                    <div class="flex justify-between text-sm text-gray-500">
                                        <span><?php echo htmlspecialchars($rgame['difficulty']); ?></span>
                                        <span><?php echo htmlspecialchars($rgame['estimatedTime']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="border rounded">
                <div class="p-4">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" data-lucide="trophy"></svg>
                        단원 전체 랭킹
                    </h2>
                </div>
                <div class="px-4 pb-4">
                    <ul class="space-y-3">
                        <?php if (empty($unitRankings)): ?>
                            <p class="text-gray-500">랭킹 정보가 없습니다.</p>
                        <?php else: ?>
                            <?php foreach ($unitRankings as $user): ?>
                                <li class="flex items-center">
                                    <span class="text-lg font-bold w-8"><?php echo intval($user['rank']); ?></span>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="w-8 h-8 rounded-full mr-3">
                                    <span class="flex-1"><?php echo htmlspecialchars($user['name']); ?></span>
                                    <span class="text-sm text-gray-500"><?php echo intval($user['score']); ?>점</span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Lucide Icons 초기화
    lucide.createIcons();

    // 탭 기능
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');

            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            tabButtons.forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-gray-200');
            });

            document.getElementById(targetTab).classList.remove('hidden');

            button.classList.remove('bg-gray-200');
            button.classList.add('bg-blue-500', 'text-white');
        });
    });

    <?php if ($role !== 'student'): ?>
    const toggleFormButton = document.getElementById('toggleFormButton');
    const newGameForm = document.getElementById('newGameForm');

    toggleFormButton.addEventListener('click', () => {
        newGameForm.classList.toggle('hidden');
    });
    <?php endif; ?>

    // 파일입력(게임파일) 버튼
    document.querySelectorAll('.code-input-button').forEach(button => {
        button.addEventListener('click', () => {
            const gameId = button.getAttribute('data-game-id');
            const existingFile = button.getAttribute('data-game-file') || '';
            const existingSavefile = button.getAttribute('data-game-savefile') || '';

            const modal = document.createElement('div');
            modal.classList = "fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50";
            modal.innerHTML = `
                <div class="bg-white p-6 rounded shadow-xl w-11/12 max-w-2xl">
                    <h2 class="text-xl font-bold mb-4">파일입력</h2>
                    <label class="block text-sm font-medium text-gray-700 mb-1">playgame.php (게임 파일)</label>
                    <textarea id="fileInput" class="w-full h-32 border border-gray-300 rounded p-2 mb-4" placeholder="여기에 file 내용을 입력하세요"></textarea>
                    <label class="block text-sm font-medium text-gray-700 mb-1">savefile.php (DB 저장용 파일)</label>
                    <textarea id="savefileInput" class="w-full h-32 border border-gray-300 rounded p-2" placeholder="여기에 savefile 내용을 입력하세요"></textarea>
                    <div class="flex justify-end space-x-4 mt-4">
                        <button id="saveCodeButton" class="px-4 py-2 bg-blue-500 text-white rounded">저장</button>
                        <button id="cancelButton" class="px-4 py-2 bg-gray-300 text-black rounded">취소</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            const fileTextarea = modal.querySelector('#fileInput');
            const savefileTextarea = modal.querySelector('#savefileInput');

            fileTextarea.value = existingFile;
            savefileTextarea.value = existingSavefile;

            const saveButton = modal.querySelector('#saveCodeButton');
            const cancelButton = modal.querySelector('#cancelButton');

            cancelButton.addEventListener('click', () => {
                document.body.removeChild(modal);
            });

            saveButton.addEventListener('click', () => {
                const fileData = fileTextarea.value;
                const savefileData = savefileTextarea.value;

                const formData = new FormData();
                formData.append('action', 'save_php_code');
                formData.append('file', fileData);
                formData.append('savefile', savefileData);
                formData.append('game_id', gameId);

                fetch('<?php echo $_SERVER['PHP_SELF']; ?>?cid=<?php echo $cid; ?>&title=<?php echo urlencode($subjectname); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('파일이 성공적으로 저장되었습니다.');
                        location.reload();
                    } else {
                        alert('파일 저장 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('파일 저장 중 오류가 발생했습니다.');
                });
            });
        });
    });

// 플레이 버튼
document.querySelectorAll('.play-button').forEach(button => {
    button.addEventListener('click', () => {
        const gameId = button.getAttribute('data-game-id');
        const url = `game_${gameId}.php`; // 변경된 부분
        window.open(url, '_blank');
    });
});
</script>
</body>
</html>
