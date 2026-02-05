<?php
/**
 * Premium Custom ID Card Rendering Engine
 * Isolated from standard flow
 */

function generateCustomIDCard($data, $layout, $isFront = true) {
    $side = $isFront ? 'front' : 'back';
    $background = $layout['background_' . $side] ?? '';
    $items = $layout['layout_json'][$side] ?? [];

    $studentName = strtoupper($data['name'] ?? 'STUDENT NAME');
    $dob = $data['dob'] ?? '';
    $bloodGroup = $data['blood'] ?? 'N/A';
    $parentName = strtoupper($data['parent'] ?? 'PARENT NAME');
    $phone = $data['phone'] ?? $data['contact'] ?? '0000000000'; // Support both keys
    $address = $data['address'] ?? 'Address goes here...';
    $studentClass = strtoupper($data['class'] ?? 'NURSERY');
    $schoolName = strtoupper($data['school_name'] ?? 'INSTITUTION NAME');
    $photoUrl = $data['photo'] ?? 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2UyZThmMCI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYzIuNjcgMCA4IDEuMzMgOCA0djJoLTE2di0yYzAtMi42NyA1LjMzLTQgOC00eiIvPjwvc3ZnPg==';
    $logoUrl = $data['logo'] ?? 'uploads/logo.png';

    ob_start();
    ?>
    <div class="id-card relative bg-white overflow-hidden shadow-2xl" 
         style="width: 325px; height: 500px; border: 1px solid #e2e8f0; border-radius: 24px; font-family: 'Poppins', sans-serif; background-image: url('<?= $background ?>'); background-size: cover; background-position: center;">
        <?php foreach ($items as $item): 
            $val = '';
            if ($item['type'] === 'text') {
                switch ($item['dataField']) {
                    case 'name': $val = $studentName; break;
                    case 'class': $val = $studentClass; break;
                    case 'parent': $val = $parentName; break;
                    case 'dob': $val = $dob; break;
                    case 'blood': $val = $bloodGroup; break;
                    case 'contact': $val = $phone; break; // Corrected from 'phone' to 'contact'
                    case 'address': $val = $address; break;
                    case 'school_name': $val = $schoolName; break;
                    case 'custom': $val = $item['text']; break;
                    default: $val = $item['text'];
                }
            ?>
            <div style="position: absolute; left: <?= $item['left'] ?>px; top: <?= $item['top'] ?>px; font-size: <?= $item['fontSize'] ?>px; color: <?= $item['fill'] ?>; font-weight: <?= $item['fontWeight'] ?? '600' ?>; width: <?= $item['width'] ?>px; line-height: 1.2; text-align: left;">
                <?= nl2br($val) ?>
            </div>
            <?php } else if ($item['type'] === 'photo') { ?>
            <div style="position: absolute; left: <?= $item['left'] ?>px; top: <?= $item['top'] ?>px; width: <?= $item['width'] ?>px; height: <?= $item['height'] ?>px; border-radius: <?= $item['rx'] ?>px; overflow: hidden; border: 3px solid #f59e0b; background-color: #f1f5f9;">
                <img src="<?= $photoUrl ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <?php } else if ($item['type'] === 'logo') { ?>
            <div style="position: absolute; left: <?= $item['left'] ?>px; top: <?= $item['top'] ?>px; width: <?= $item['width'] ?>px; height: <?= $item['height'] ?>px; border-radius: <?= $item['rx'] ?>px; overflow: hidden; border: 2px solid #f59e0b; background-color: #fff;">
                <img src="<?= $logoUrl ?>" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <?php } 
        endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
