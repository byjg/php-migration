---
sidebar_position: 3
title: Migration Scripts
description: Learn how to write and organize database migration scripts
---

# Writing Migration Scripts

## Script Types

The migration system uses three types of SQL scripts:

1. **Base Script** (`base.sql`): Contains the initial database schema
2. **Up Scripts** (`migrations/up/*.sql`): Scripts to upgrade database version
3. **Down Scripts** (`migrations/down/*.sql`): Scripts to downgrade database version (optional)

## Script Naming

Scripts in the `up` and `down` folders must follow the naming convention:

- Format: `NNNNN[-description].sql`
- Example: `00042-add-user-table.sql` or `00042.sql`

The number represents the version that the script will migrate to (up) or from (down).

## Multi-Developer Workflow

:::info Handling Multiple Developers
When multiple developers work on different branches, use the `-dev` suffix to avoid version conflicts.
:::

When multiple developers work on different branches:

1. Use `-dev` suffix for work in progress:
   ```
   43-dev.sql  # Developer 1's WIP migration
   43-dev.sql  # Developer 2's WIP migration (different branch)
   ```

2. When merging:
   - First developer to merge renames to final version:
     ```
     git mv 43-dev.sql 43.sql
     ```
   - Second developer must update their version:
     ```
     git mv 43-dev.sql 44-dev.sql
     ```

## Script Writing Tips

### General Tips

1. Always include a description using SQL comments:
   ```sql
   -- @description: Add user authentication fields
   ALTER TABLE users ADD COLUMN password_hash VARCHAR(255);
   ```

2. Make scripts idempotent when possible:
   ```sql
   -- Check if column exists before adding
   IF NOT EXISTS (
       SELECT 1 FROM information_schema.columns 
       WHERE table_name = 'users' AND column_name = 'password_hash'
   ) THEN
       ALTER TABLE users ADD COLUMN password_hash VARCHAR(255);
   END IF;
   ```

3. Include corresponding down migration:
   ```sql
   -- Up: 00042-add-user-auth.sql
   ALTER TABLE users ADD COLUMN password_hash VARCHAR(255);
   
   -- Down: 00041.sql
   ALTER TABLE users DROP COLUMN password_hash;
   ```

### PostgreSQL-Specific Tips

:::warning Important for PostgreSQL Functions
Always add `--` comments after semicolons inside function definitions to prevent parsing errors.
:::

1. Use `--` after semicolons in functions:
   ```sql
   CREATE FUNCTION update_timestamp() RETURNS trigger AS $$
   BEGIN
       NEW.updated_at = CURRENT_TIMESTAMP; --
       RETURN NEW; --
   END; --
   $$ LANGUAGE plpgsql;
   ```

2. Avoid the colon operator, use CAST instead:
   ```sql
   -- DO
   SELECT CAST(created_at AS DATE)
   
   -- DON'T
   SELECT created_at::DATE
   ```

### MySQL-Specific Tips

1. Remember that MySQL doesn't support transactions for DDL:
   ```sql
   -- These will run without transaction support
   ALTER TABLE users ADD COLUMN email VARCHAR(255);
   CREATE INDEX idx_user_email ON users(email);
   ```

2. Use IF EXISTS/IF NOT EXISTS clauses:
   ```sql
   DROP TABLE IF EXISTS temporary_table;
   CREATE TABLE IF NOT EXISTS new_table ( ... );
   ```

## Best Practices

1. **One Change Per Migration**
   - Keep migrations focused and atomic
   - Makes rollbacks and debugging easier

2. **Include Description Comments**
   - Document what the migration does
   - Add any necessary warnings or prerequisites

3. **Test Both Up and Down**
   - Verify migrations work in both directions
   - Test with representative data

4. **Use Database-Specific Features Carefully**
   - Document when using database-specific SQL
   - Provide alternatives when possible

5. **Version Control**
   - Never modify committed migrations
   - Create new migrations for changes
   - Use descriptive file names 