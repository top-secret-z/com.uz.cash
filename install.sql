-- cash balance in user table
ALTER TABLE wcf1_user ADD cashBalance TEXT;

-- cash
DROP TABLE IF EXISTS cash1_cash;
CREATE TABLE cash1_cash (
    cashID                    INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    amount                    DOUBLE(12,2) NOT NULL DEFAULT 0.0,
    currency                VARCHAR(3) NOT NULL DEFAULT 'EUR',
    comment                    VARCHAR(255) NOT NULL DEFAULT '',
    time                    INT(10) NOT NULL DEFAULT 0,
    type                    VARCHAR(25) NOT NULL DEFAULT '',
    typeID                    INT(10) NOT NULL DEFAULT 0,
    userID                    INT(10),
    username                VARCHAR(255) NOT NULL DEFAULT '',
    isDeleted                TINYINT(1) NOT NULL DEFAULT 0,

    KEY currency,
    KEY time,
    KEY type,
    KEY userID,
    KEY isDeleted
);

DROP TABLE IF EXISTS cash1_cash_posting;
CREATE TABLE cash1_cash_posting (
    postingID                INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    categoryID                INT(10),
    type                    VARCHAR(10) NOT NULL DEFAULT 'expense',
    time                    INT(10) NOT NULL DEFAULT 0,
    userID                    INT(10),
    username                VARCHAR(255) NOT NULL DEFAULT '',
    amount                    DOUBLE(12,2) NOT NULL DEFAULT 0.0,
    currency                VARCHAR(3) NOT NULL DEFAULT 'EUR',
    subject                    VARCHAR(255) NOT NULL DEFAULT '',
    message                    MEDIUMTEXT,
    hasEmbeddedObjects        TINYINT(1) NOT NULL DEFAULT 0,
    attachments                SMALLINT(5) NOT NULL DEFAULT 0,
    enableHtml                TINYINT(1) NOT NULL DEFAULT 0,

    KEY time,
    KEY type,
    KEY userID,
    KEY amount,
    KEY currency
);

DROP TABLE IF EXISTS cash1_cash_claim;
CREATE TABLE cash1_cash_claim (
    claimID                    INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    categoryID                INT(10),
    isDisabled                TINYINT(1) NOT NULL DEFAULT 0,
    time                    INT(10) NOT NULL DEFAULT 0,
    userID                    INT(10),
    username                VARCHAR(255) NOT NULL DEFAULT '',
    amount                    DOUBLE(12,2) NOT NULL DEFAULT 0.0,
    currency                VARCHAR(3) NOT NULL DEFAULT 'EUR',
    excludedPaymentMethods    TEXT,
    subject                    VARCHAR(255) NOT NULL DEFAULT '',
    message                    MEDIUMTEXT,
    hasEmbeddedObjects        TINYINT(1) NOT NULL DEFAULT 0,
    attachments                SMALLINT(5) NOT NULL DEFAULT 0,
    enableHtml                TINYINT(1) NOT NULL DEFAULT 0,
    users                    TEXT,
    frequency                VARCHAR(25) NOT NULL DEFAULT 'once',
    executions                INT(10) NOT NULL DEFAULT 1,
    executionCount            INT(10) NOT NULL DEFAULT 0,
    executionTime            INT(10) NOT NULL DEFAULT 0,
    nextExecution            INT(10) NOT NULL DEFAULT 0,
    timezone                VARCHAR(50) NOT NULL DEFAULT 'UTC',

    KEY isDisabled,
    KEY time,
    KEY amount,
    KEY currency,
    KEY frequency,
    KEY executions
);

DROP TABLE IF EXISTS cash1_cash_claim_user;
CREATE TABLE cash1_cash_claim_user (
    userClaimID                INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    claimID                    INT(10),
    time                    INT(10) NOT NULL DEFAULT 0,
    userID                    INT(10),
    amount                    DOUBLE(12,2) NOT NULL DEFAULT 0.0,
    currency                VARCHAR(3) NOT NULL DEFAULT 'EUR',
    executionCount            INT(10) NOT NULL DEFAULT 1,
    status                    TINYINT(1) NOT NULL DEFAULT 0,
    isChanged                TINYINT(1) NOT NULL DEFAULT 0,
    subject                    VARCHAR(255) NOT NULL DEFAULT '',
    isTransfer                TINYINT(1) NOT NULL DEFAULT 0,

    UNIQUE KEY (claimID, userID, executionCount),
    KEY time,
    KEY userID,
    KEY amount,
    KEY currency,
    KEY status,
    KEY isTransfer
);

DROP TABLE IF EXISTS cash1_cash_credit;
CREATE TABLE cash1_cash_credit (
    creditID                INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    categoryID                INT(10),
    isDisabled                TINYINT(1) NOT NULL DEFAULT 0,
    time                    INT(10) NOT NULL DEFAULT 0,
    userID                    INT(10),
    username                VARCHAR(255) NOT NULL DEFAULT '',
    amount                    DOUBLE(12,2) NOT NULL DEFAULT 0.0,
    currency                VARCHAR(3) NOT NULL DEFAULT 'EUR',
    subject                    VARCHAR(255) NOT NULL DEFAULT '',
    message                    MEDIUMTEXT,
    hasEmbeddedObjects        TINYINT(1) NOT NULL DEFAULT 0,
    attachments                SMALLINT(5) NOT NULL DEFAULT 0,
    enableHtml                TINYINT(1) NOT NULL DEFAULT 0,
    users                    TEXT,
    frequency                VARCHAR(25) NOT NULL DEFAULT 'once',
    executions                INT(10) NOT NULL DEFAULT 1,
    executionCount            INT(10) NOT NULL DEFAULT 0,
    executionTime            INT(10) NOT NULL DEFAULT 0,
    nextExecution            INT(10) NOT NULL DEFAULT 0,
    timezone                VARCHAR(50) NOT NULL DEFAULT 'UTC',

    KEY isDisabled,
    KEY time,
    KEY amount,
    KEY currency,
    KEY frequency,
    KEY executions
);

DROP TABLE IF EXISTS cash1_cash_credit_user;
CREATE TABLE cash1_cash_credit_user (
    userCreditID            INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    creditID                INT(10),
    time                    INT(10) NOT NULL DEFAULT 0,
    userID                    INT(10),
    amount                    DOUBLE(12,2) NOT NULL DEFAULT 0.0,
    currency                VARCHAR(3) NOT NULL DEFAULT 'EUR',
    executionCount            INT(10) NOT NULL DEFAULT 1,
    status                    TINYINT(1) NOT NULL DEFAULT 0,
    isChanged                TINYINT(1) NOT NULL DEFAULT 0,
    subject                    VARCHAR(255) NOT NULL DEFAULT '',

    UNIQUE KEY (creditID, userID, executionCount),
    KEY time,
    KEY userID,
    KEY amount,
    KEY currency,
    KEY status
);

DROP TABLE IF EXISTS cash1_cash_transaction_log;
CREATE TABLE cash1_cash_transaction_log (
    logID                        INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    logTime                        INT(10) NOT NULL DEFAULT 0,
    userID                        INT(10),
    userClaimID                    INT(10),
    paymentMethodObjectTypeID    INT(10) NOT NULL,
    logMessage                    MEDIUMTEXT,
    transactionID                MEDIUMTEXT,
    transactionDetails            MEDIUMTEXT,

    KEY userID,
    KEY userClaimID,
    KEY paymentMethodTypeID
);

ALTER TABLE cash1_cash ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_posting ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_posting ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_claim ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_claim ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_claim_user ADD FOREIGN KEY (claimID) REFERENCES cash1_cash_claim (claimID) ON DELETE CASCADE;
ALTER TABLE cash1_cash_claim_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE cash1_cash_credit ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_credit ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_credit_user ADD FOREIGN KEY (creditID) REFERENCES cash1_cash_credit (creditID) ON DELETE CASCADE;
ALTER TABLE cash1_cash_credit_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE cash1_cash_transaction_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_transaction_log ADD FOREIGN KEY (userClaimID) REFERENCES cash1_cash_claim_user (userClaimID) ON DELETE SET NULL;
ALTER TABLE cash1_cash_transaction_log ADD FOREIGN KEY (paymentMethodObjectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
