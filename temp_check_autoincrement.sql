SELECT
    CONCAT(
        'ALTER TABLE `', c.TABLE_NAME, '` MODIFY `', c.COLUMN_NAME, '` ',
        c.COLUMN_TYPE, ' NOT NULL AUTO_INCREMENT;'
    ) AS sql_statement
FROM information_schema.COLUMNS c
INNER JOIN (
    SELECT TABLE_NAME, COUNT(*) as pk_count
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'cycloid_auditorias'
      AND CONSTRAINT_NAME = 'PRIMARY'
    GROUP BY TABLE_NAME
    HAVING pk_count = 1
) pk ON c.TABLE_NAME = pk.TABLE_NAME
WHERE c.TABLE_SCHEMA = 'cycloid_auditorias'
  AND c.COLUMN_KEY = 'PRI'
  AND c.EXTRA NOT LIKE '%auto_increment%'
  AND c.DATA_TYPE IN ('tinyint', 'smallint', 'mediumint', 'int', 'bigint')
ORDER BY c.TABLE_NAME;
