<?php

namespace ByJG\DbMigration;

enum MigrationStatus: string
{
    case Unknown = "unknown";
    case PartialUp = "partial up";
    case PartialDown = "partial down";
    case Complete = "complete";
}
