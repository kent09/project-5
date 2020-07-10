<?php

namespace App\Helpers;

class CsvStatus
{
    /**
     * CSV Import status
     */
    const IMPORTING = 0;
    const IMPORT_FINISHED = 1;
    const RUNNING_SYNC = 2;
    const FINISHED_SYNC = 3;
    const INFS_RUNNING_SYNC = 4;
    const INFS_FINISHED_SYNC = 5;
    const INFS_FAILED_SYNC = 6;
    const IMPORT_ERROR = 7;
    const CANCELLED = 8;

    /**
     * CSV record status
    */
    const CSV_RECORD_PENDING = 0;
    const CSV_RECORD_IMPORT_PROCESSING = 1;
    const CSV_RECORD_IMPORT_SUCCESS = 2;
    const CSV_RECORD_IMPORT_ERROR = 3;
    const CSV_RECORD_INFS_RUNNING_SYNC = 4;
    const CSV_RECORD_INFS_FINISHED_SYNC = 5;
    const CSV_RECORD_INFS_FAILED_SYNC = 6;

    /**
     * Obj Types
     */
    const TYPE_COMPANY = 'company';
    const TYPE_CONTACT = 'contacts';

    /**
     * Infs Types
     */
    const INFS_COMPANY = 'Company';
    const INFS_CONTACT = 'Contact';
}


