<?php


namespace Gabeta\LaraPnn\Console;

use App\User;
use Gabeta\LaraPnn\Facades\LaraPnn;
use Gabeta\LaraPnn\LaraPnnAbstract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LaraPnnMigrateCommand extends Command
{
    protected $signature = 'larapnn:migrate {model}';

    protected $description = 'Migrate Your tel number for new plan';

    private $model;

    public function handle()
    {
        $argument = $this->argument('model');

        $this->model = app($argument);

        if (! ($this->model instanceof LaraPnnAbstract)) {
            throw new \InvalidArgumentException($argument.' must implement '.LaraPnnAbstract::class);
        }

        $start = microtime(true);

        $queryResults = $this->model->all();

        $eligibleModels = [];
        $eligibleModelsColumns = [];
        foreach ($queryResults as $result) {
            $eligibleFields = $result->getEligibleFields(true);

            if (count($eligibleFields)) {
                $eligibleModels[] = $result;
                foreach ($eligibleFields as $f) {
                    $eligibleModelsColumns[$f][] = $result->getKey();
                }
            }
        }

        $eligibleModelsLength = count($eligibleModels);
        $eligibleModelsColumnsLength = count(call_user_func_array('array_merge', $eligibleModelsColumns));

        $this->line("\n <options=bold,reverse;fg=green> Migration Stats for {$this->model->getTable()} table: </> \n");
        $this->line("<options=bold>Eligible row:</> {$eligibleModelsLength}");
        $this->line("<options=bold>Eligible tel number column:</> {$eligibleModelsColumnsLength}");

        if (!$eligibleModels) {

            $this->line("\n <options=bold,reverse;fg=red> Nothing to migrate 😳</> \n");

            return false;
        }

        $migrate = $this->ask('You do want to migrates ? [yes|no]', 'yes');

        if ($migrate === 'yes') {
            $this->migrate($eligibleModels, $eligibleModelsColumns);

            $time = number_format(microtime(true) - $start, 3);

            $this->line("Execution time {$time} sécondes\n");
        }
    }

    private function migrate($models, $columns)
    {
        $this->line("Begin migrate. Please wait ! \n");

        foreach ($columns as $column => $modelIds) {
            $this->migrateByEligibleColumn($models, $column, $modelIds);
        }

        $this->line("<options=bold,reverse;fg=yellow> Migrate successful.  </> \n");
    }

    private function migrateByEligibleColumn($models, $column, $modelIds)
    {
        if (count($modelIds)) {

            $query = "UPDATE {$this->model->getTable()} SET `{$column}` =";

            $cases = [];
            $ids = [];
            $params = [];
            foreach ($models as $model) {
                if (in_array($model->getKey(), $modelIds)) {
                    $cases[] = "WHEN {$model->getKey()} then ?";
                    $params[] = LaraPnn::translateToNewPnnFormat($model->{$column});
                    $ids[] = $model->getKey();
                }
            }

            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);

            $query .= " CASE `{$this->model->getKeyName()}` {$cases} END WHERE `{$this->model->getKeyName()}` IN ({$ids})";

            DB::update($query, $params);
        }
    }
}
