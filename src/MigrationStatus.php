<?php

namespace ByJG\DbMigration;

enum MigrationStatus: string
{
    case unknown = "unknown";
    case partialUp = "partial up";
    case partialDown = "partial down";
    case complete = "complete";
}
