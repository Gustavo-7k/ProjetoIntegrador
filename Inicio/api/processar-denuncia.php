<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !isAdmin()) {
    sendJSONResponse(['success' => false, 'message' => 'Acesso negado'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJSONResponse(['success' => false, 'message' => 'Método não permitido'], 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['denuncia_id']) || !isset($input['acao'])) sendJSONResponse(['success' => false, 'message' => 'Dados inválidos'], 400);


$denuncia_id = (int)$input['denuncia_id'];
$acao = $input['acao']; // 'aprovar' or 'rejeitar' or 'liberar'

try {
    $pdo = getDBConnection();

    // Fetch report
    $stmt = $pdo->prepare('SELECT * FROM reports WHERE id = ?');
    $stmt->execute([$denuncia_id]);
    $report = $stmt->fetch();
    if (!$report) sendJSONResponse(['success' => false, 'message' => 'Denúncia não encontrada'], 404);

    if ($acao === 'aprovar') {
        // Mark report as reviewed/resolved and mark comment as rejected
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('UPDATE comments SET status = "rejected" WHERE id = ?');
        $stmt->execute([$report['comment_id']]);

        $stmt = $pdo->prepare('UPDATE reports SET status = "resolved", reviewed_by = ?, reviewed_at = NOW(), admin_notes = ? WHERE id = ?');
        $stmt->execute([$_SESSION['user_id'], isset($input['admin_notes']) ? trim($input['admin_notes']) : null, $denuncia_id]);
        $pdo->commit();

        sendJSONResponse(['success' => true]);
    } elseif ($acao === 'rejeitar') {
        $stmt = $pdo->prepare('UPDATE reports SET status = "reviewed", reviewed_by = ?, reviewed_at = NOW(), admin_notes = ? WHERE id = ?');
        $stmt->execute([$_SESSION['user_id'], isset($input['admin_notes']) ? trim($input['admin_notes']) : null, $denuncia_id]);
        sendJSONResponse(['success' => true]);
    } elseif ($acao === 'liberar') {
        // Re-approve comment
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('UPDATE comments SET status = "approved" WHERE id = ?');
        $stmt->execute([$report['comment_id']]);
        $stmt = $pdo->prepare('UPDATE reports SET status = "resolved", reviewed_by = ?, reviewed_at = NOW(), admin_notes = ? WHERE id = ?');
        $stmt->execute([$_SESSION['user_id'], isset($input['admin_notes']) ? trim($input['admin_notes']) : null, $denuncia_id]);
        $pdo->commit();
        sendJSONResponse(['success' => true]);
    } else {
        sendJSONResponse(['success' => false, 'message' => 'Ação desconhecida'], 400);
    }

} catch (Exception $e) {
    error_log('Erro processar-denuncia: ' . $e->getMessage());
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    sendJSONResponse(['success' => false, 'message' => 'Erro interno'], 500);
}

?>
