IF (EXISTS (SELECT * 
                 FROM INFORMATION_SCHEMA.TABLES 
                 WHERE TABLE_SCHEMA = 'yespoint_salavrefac' 
                 AND  TABLE_NAME = 'inventarioosquimicos1'))
BEGIN
    SELECT count(id) FROM `yespoint_salavrefac`.`inventarioosquimicos1` WHERE sucursal_id=1;
END

EXEC sp_tables 
  @table_name = 'inventarioosquimicos1',  
  @table_owner = 'dbo'


SELECT
  object_id  
FROM sys.tables
WHERE name = 'inventarioosquimicos1'
AND SCHEMA_NAME(schema_id) = 'yespoint_salavrefac';