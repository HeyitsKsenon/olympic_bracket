<?php

ini_set('memory_limit', '4096M');

if ($argc !== 2) {
    echo "Usage: php generate_bracket.php <number_of_teams>\n";
    exit(1);
}

$numberOfTeams = (int)$argv[1];

// Проверка, что количество команд - степень двойки и в пределах допустимого диапазона
if ($numberOfTeams <= 0 || ($numberOfTeams & ($numberOfTeams - 1)) !== 0 || $numberOfTeams > (1 << 31)) {
    echo "Please provide a power of 2 between 1 and 2^31.\n";
    exit(1);
}

echo "Start script...\n";

function drawBracket($teams, $x, $y, $image, $level = 0) {
    // Начальная ширина, высота и промежуток между матчами
    $MATCH_WIDTH = 100; 
    $MATCH_HEIGHT = 50;
    $SPACING = 20;

    if (count($teams) % 2 !== 0) {
        throw new Exception("Количество команд должно быть четным.");
    }

    $numberOfMatches = count($teams) / 2;
    $nextRoundTeams = [];
    $matchCoordinatesX = [];

    // Определяем ширину матча для текущего уровня
    // Вычисляем начальную позицию X
    $currentCellWidth = ($MATCH_WIDTH * pow(2, $level)) + ($SPACING * (pow(2, $level) - 1));

    if ($level == 0) {
        $margin = $SPACING;
    } else {
        $margin = $currentCellWidth - $MATCH_WIDTH;
    }

    for ($i = 0; $i < $numberOfMatches; $i++) {
        // Вычисляем координаты X для каждого матча
        $startX = $i * $currentCellWidth + ($i + 1) * $SPACING;
        $matchCoordinatesX[$i] = $startX + ($margin/2);

        // Рисуем прямоугольник для матча
        imagefilledrectangle($image, $matchCoordinatesX[$i], $y - $MATCH_HEIGHT,
                             $matchCoordinatesX[$i] + $MATCH_WIDTH,
                             $y,
                             imagecolorallocate($image, 255, 255, 255));
        imagerectangle($image, $matchCoordinatesX[$i], $y - $MATCH_HEIGHT,
                       $matchCoordinatesX[$i] + $MATCH_WIDTH,
                       $y,
                       imagecolorallocate($image, 0, 0, 0));
        // Подписываем данные матча
        imagestring($image, 5, 
                    $matchCoordinatesX[$i] + ($SPACING), 
                    $y - $MATCH_HEIGHT, 
                    "Match " . ($i + 1), 
                    imagecolorallocate($image, 0, 0, 0));
        imagestring($image, 5, 
                    $matchCoordinatesX[$i] + ($SPACING), 
                    $y - $MATCH_HEIGHT + $SPACING * 0.75, 
                    $teams[$i * 2] . " vs " . $teams[$i * 2 + 1], 
                    imagecolorallocate($image, 0, 0, 0));

                    $winnerIndex = rand(0, 1);

        imagestring($image, 5, 
                    $matchCoordinatesX[$i] + ($SPACING / 2), 
                    $y - $MATCH_HEIGHT + (1.5 * $SPACING), 
                    "Winner: " . $teams[$i * 2 + $winnerIndex], 
                    imagecolorallocate($image, 0, 0, 0));
        
        // Добавляем победителя в следующий раунд
        $nextRoundTeams[] = $teams[$i * 2 + $winnerIndex];
    }

    if (count($nextRoundTeams) > 1) {
        // Переход к следующему уровню
        $nextLevelY = $y - (2 * $MATCH_HEIGHT);
        drawBracket($nextRoundTeams, $x, $nextLevelY, $image, $level + 1);

        // Рисуем линии между матчами
        for ($i = 0; $i < count($nextRoundTeams); $i += 2) {
            if (isset($matchCoordinatesX[$i])) {
                // Начало линии от первого матча
                $lineStartX1 = $matchCoordinatesX[$i] + ($MATCH_WIDTH / 2);
                imageline($image, 
                          $lineStartX1, 
                          $y - $MATCH_HEIGHT, 
                          $lineStartX1 + ($currentCellWidth + $SPACING) / 2, 
                          $nextLevelY, 
                          imagecolorallocate($image, 0, 0, 0));
            }
            
            if (isset($matchCoordinatesX[$i + 1])) {
                // Начало линии от второго матча
                $lineStartX2 = $matchCoordinatesX[$i + 1] + ($MATCH_WIDTH / 2);
                imageline($image, 
                          $lineStartX2, 
                          $y - $MATCH_HEIGHT, 
                          $lineStartX1 + ($currentCellWidth + $SPACING) / 2,
                          $nextLevelY, 
                          imagecolorallocate($image, 0, 0, 0));
            }
        }
    }
}

// Устанавливаем размеры изображения
// Ширина изображения зависит от количества команд
$imageWidth = max(1000, (100 + 20) * ($numberOfTeams / 2)); 
// Высота изображения зависит от количества уровней
$imageHeight = max(600, (50 + 100) * log($numberOfTeams, 2)); 
$image = imagecreatetruecolor($imageWidth, $imageHeight);
$backgroundColor = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 0, 0, $imageWidth - 1, $imageHeight - 1, $backgroundColor);

// Центрируем координаты по изображению
// Высчитываем центр по X
$centerX = ($imageWidth - (($numberOfTeams / 2) * (100 + 20))) / 2; 
// Начальная Y координата для первого раунда
$startY = $imageHeight - (50 + 100);

// Генерируем матчи для первого раунда
drawBracket(range(1, $numberOfTeams), $centerX, $startY, $image);

// Создаем папку 'files'
if (!is_dir('files')) {
    mkdir('files', 0755, true); 
}

// Создаем уникальное имя файла с использованием временной метки
$filename = 'files/vertical_bracket_' . time() . '.png';

echo "Saving image as $filename...\n";

if (!imagepng($image, $filename)) {
    echo "Failed to save image.\n";
    exit(1);
}

imagedestroy($image);

echo "Bracket generated in $filename.\nScript finished.\n";
