<?php
function getAlerts($conn) {
    $alerts = [];
    $today = date('Y-m-d');
    $soon = date('Y-m-d', strtotime('+30 days'));

    // Asigurari expirate sau care expira curand
    $r = $conn->query("SELECT a.*, m.nr_inmatriculare FROM asigurari a JOIN masini m ON m.id = a.masina_id WHERE a.data_expirare <= '$soon' ORDER BY a.data_expirare ASC");
    while ($row = $r->fetch_assoc()) {
        $expired = $row['data_expirare'] < $today;
        $alerts[] = [
            'type'    => $expired ? 'danger' : 'warning',
            'iconKey' => 'shield',
            'message' => "Asigurare {$row['tip']} - {$row['nr_inmatriculare']}",
            'date'    => $row['data_expirare'],
            'expired' => $expired,
        ];
    }

    // Viniete expirate sau care expira curand
    $r = $conn->query("SELECT v.*, m.nr_inmatriculare FROM viniete v JOIN masini m ON m.id = v.masina_id WHERE v.data_expirare <= '$soon' ORDER BY v.data_expirare ASC");
    while ($row = $r->fetch_assoc()) {
        $expired = $row['data_expirare'] < $today;
        $alerts[] = [
            'type'    => $expired ? 'danger' : 'warning',
            'iconKey' => 'tag',
            'message' => "Vinietă {$row['tara']} - {$row['nr_inmatriculare']}",
            'date'    => $row['data_expirare'],
            'expired' => $expired,
        ];
    }

    // Permise clienti expirate sau care expira curand
    $r = $conn->query("SELECT * FROM clienti WHERE data_expirare_permis IS NOT NULL AND data_expirare_permis <= '$soon' ORDER BY data_expirare_permis ASC");
    while ($row = $r->fetch_assoc()) {
        $expired = $row['data_expirare_permis'] < $today;
        $alerts[] = [
            'type'    => $expired ? 'danger' : 'warning',
            'iconKey' => 'user',
            'message' => "Permis expirat - {$row['nume']}",
            'date'    => $row['data_expirare_permis'],
            'expired' => $expired,
        ];
    }

    // ITP expirat sau care expira curand
    $r = $conn->query("SELECT i.*, m.nr_inmatriculare FROM itp i JOIN masini m ON m.id = i.masina_id WHERE i.data_expirare <= '$soon' ORDER BY i.data_expirare ASC");
    while ($row = $r->fetch_assoc()) {
        $expired = $row['data_expirare'] < $today;
        $alerts[] = [
            'type'    => $expired ? 'danger' : 'warning',
            'iconKey' => 'search',
            'message' => "ITP - {$row['nr_inmatriculare']}",
            'date'    => $row['data_expirare'],
            'expired' => $expired,
        ];
    }

    return $alerts;
}
?>
