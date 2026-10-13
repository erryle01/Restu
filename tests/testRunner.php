<?php

declare(strict_types=1);

/**
 * Restu CLI Test Runner
 *
 * Run from:
 * C:\xampp\htdocs\Restu
 *
 * Command:
 * php tests/TestRunner.php
 */

define('TEST_ROOT', dirname(__DIR__));

require_once TEST_ROOT . '/config/config.php';

require_once TEST_ROOT . '/core/Database.php';
require_once TEST_ROOT . '/core/Model.php';
require_once TEST_ROOT . '/core/Session.php';
require_once TEST_ROOT . '/core/Auth.php';
require_once TEST_ROOT . '/modules/Payroll/Services/SalaryCalculator.php';
require_once TEST_ROOT . '/modules/Payroll/Services/OvertimeCalculator.php';
require_once TEST_ROOT . '/modules/Payroll/Services/DeductionCalculator.php';
require_once TEST_ROOT . '/modules/Payroll/Services/SSSCalculator.php';
require_once TEST_ROOT . '/modules/Payroll/Services/PhilHealthCalculator.php';
require_once TEST_ROOT . '/modules/Payroll/Services/PagIBIGCalculator.php';
require_once TEST_ROOT . '/modules/Payroll/Services/TaxCalculator.php';
require_once TEST_ROOT . '/modules/Payroll/Services/PayrollEngine.php';

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/DatabaseTest.php';
require_once __DIR__ . '/AuthTest.php';
require_once __DIR__ . '/RBACTest.php';
require_once __DIR__ . '/EmployeeTest.php';
require_once __DIR__ . '/ScheduleTest.php';
require_once __DIR__ . '/AttendanceTest.php';
require_once __DIR__ . '/PayrollCalculationTest.php';

class TestRunner
{
    private array $tests = [];

    private int $passed = 0;
    private int $failed = 0;
    private int $skipped = 0;

    public function add(string $group, string $name, callable $test): void
    {
        $this->tests[] = [
            'group' => $group,
            'name'  => $name,
            'test'  => $test
        ];
    }

    public function run(): void
    {
        $this->printHeader();

        $currentGroup = '';

        foreach ($this->tests as $test) {

            if ($currentGroup !== $test['group']) {

                $currentGroup = $test['group'];

                echo PHP_EOL;
                echo "\033[1;36m[" .
                    strtoupper($currentGroup) .
                    "]\033[0m" .
                    PHP_EOL;
            }

            try {

                $result = call_user_func($test['test']);

                if ($result === TestCase::SKIP) {

                    $this->skipped++;

                    echo "  \033[1;33m- SKIP\033[0m ";
                    echo $test['name'];
                    echo PHP_EOL;

                    continue;
                }

                $this->passed++;

                echo "  \033[1;32m✓ PASS\033[0m ";
                echo $test['name'];
                echo PHP_EOL;

            } catch (Throwable $e) {

                $this->failed++;

                echo "  \033[1;31m✗ FAIL\033[0m ";
                echo $test['name'];
                echo PHP_EOL;

                echo "    \033[0;31m";
                echo $e->getMessage();
                echo "\033[0m";
                echo PHP_EOL;
            }
        }

        $this->printSummary();
    }

    private function printHeader(): void
    {
        echo PHP_EOL;

        echo "\033[1;36m";
        echo "========================================";
        echo "\033[0m";
        echo PHP_EOL;

        echo "\033[1;36m";
        echo "        RESTU SYSTEM CLI TEST";
        echo "\033[0m";
        echo PHP_EOL;

        echo "\033[1;36m";
        echo "========================================";
        echo "\033[0m";
        echo PHP_EOL;

        echo "Project: " . TEST_ROOT . PHP_EOL;
        echo "PHP:     " . PHP_VERSION . PHP_EOL;
    }

    private function printSummary(): void
    {
        echo PHP_EOL;

        echo "\033[1;36m";
        echo "========================================";
        echo "\033[0m";
        echo PHP_EOL;

        echo "\033[1mRESULT\033[0m";
        echo PHP_EOL;
        echo PHP_EOL;

        echo "PASSED  : \033[1;32m" .
            $this->passed .
            "\033[0m" .
            PHP_EOL;

        echo "FAILED  : \033[1;31m" .
            $this->failed .
            "\033[0m" .
            PHP_EOL;

        echo "SKIPPED : \033[1;33m" .
            $this->skipped .
            "\033[0m" .
            PHP_EOL;

        echo PHP_EOL;

        if ($this->failed === 0) {

            echo "\033[1;32m";
            echo "STATUS  : PASS";
            echo "\033[0m";

        } else {

            echo "\033[1;31m";
            echo "STATUS  : FAIL";
            echo "\033[0m";
        }

        echo PHP_EOL;

        echo "\033[1;36m";
        echo "========================================";
        echo "\033[0m";
        echo PHP_EOL;
    }
}


/*
|--------------------------------------------------------------------------
| Register Tests
|--------------------------------------------------------------------------
*/

$runner = new TestRunner();

DatabaseTest::register($runner);
AuthTest::register($runner);
RBACTest::register($runner);
EmployeeTest::register($runner);
ScheduleTest::register($runner);
AttendanceTest::register($runner);
PayrollCalculationTest::register($runner);

$runner->run();

exit(0);