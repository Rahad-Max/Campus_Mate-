<?php
namespace App\Models;

use App\Core\Model;

class AcademicDocument extends Model
{
    protected string $table = 'academic_documents';
    protected array $fillable = [
        'title', 'document_code', 'category', 'department_id',
        'file_path', 'file_type', 'file_size_kb', 'version',
        'uploaded_by', 'processing_status', 'is_active'
    ];

    public function getChunks(int $documentId): array
    {
        $sql = "SELECT * FROM `document_chunks` WHERE document_id = :did ORDER BY chunk_index ASC";
        return $this->db->fetchAll($sql, [':did' => $documentId]);
    }

    public function searchChunks(string $query, int $limit = 5): array
    {
        $sql = "SELECT dc.*, ad.title as document_title, ad.document_code, ad.category,
                       MATCH(dc.content, dc.chunk_title) AGAINST(:q IN NATURAL LANGUAGE MODE) AS relevance
                FROM `document_chunks` dc
                JOIN `academic_documents` ad ON ad.id = dc.document_id
                WHERE ad.is_active = 1
                  AND MATCH(dc.content, dc.chunk_title) AGAINST(:q2 IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC
                LIMIT :lim";

        // Fallback to LIKE if fulltext returns 0 results for very short or specific search terms
        $params = [':q' => $query, ':q2' => $query];
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':q', $query, \PDO::PARAM_STR);
        $stmt->bindValue(':q2', $query, \PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        if (empty($results)) {
            $likeSql = "SELECT dc.*, ad.title as document_title, ad.document_code, ad.category, 1.0 as relevance
                        FROM `document_chunks` dc
                        JOIN `academic_documents` ad ON ad.id = dc.document_id
                        WHERE ad.is_active = 1 AND (dc.content LIKE :term OR dc.chunk_title LIKE :term2)
                        LIMIT :lim";
            $stmt = $this->db->getConnection()->prepare($likeSql);
            $stmt->bindValue(':term', "%{$query}%", \PDO::PARAM_STR);
            $stmt->bindValue(':term2', "%{$query}%", \PDO::PARAM_STR);
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
        }

        return $results;
    }
}
