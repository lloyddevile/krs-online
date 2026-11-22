<?php
function batas_sks($semester, $ipk_kumulatif, $ambang_ipk) {
    if ((int)$semester >= 3 && is_numeric($ipk_kumulatif) && is_numeric($ambang_ipk)
        && (float)$ipk_kumulatif > (float)$ambang_ipk) {
        return 24;
    }

    return 22;
}
?>
