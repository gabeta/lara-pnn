<?php


namespace Gabeta\LaraPnn\Console;


use Gabeta\LaraPnn\LaraPnnAbstract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

abstract class LaraPnnCommand extends Command
{
    protected $model;

    public function handle()
    {
        $argument = $this->argument('model');

        $this->model = app($argument);

        if (! ($this->model instanceof LaraPnnAbstract)) {
            throw new \InvalidArgumentException($argument.' must implement '.LaraPnnAbstract::class);
        }

        $queryResults = $this->model->all();

        $eligibleModels = [];
        $eligibleModelsColumns = [];
        foreach ($queryResults as $result) {
            $eligibleFields = $this->getEligibleFields($result);

            if (count($eligibleFields)) {
                $eligibleModels[] = $result;
                foreach ($eligibleFields as $f) {
                    $eligibleModelsColumns[$f][] = $result->getKey();
                }
            }
        }

        $eligibleModelsLength = count($eligibleModels);
        $eligibleModelsColumnsLength = count(call_user_func_array('array_merge', $eligibleModelsColumns));

        $this->line("\n <options=bold,reverse;fg=green>{$this->description} | Stats for {$this->model->getTable()} table: </> \n");
        $this->line("<options=bold>Eligible row:</> {$eligibleModelsLength}");
        $this->line("<options=bold>Eligible tel number column:</> {$eligibleModelsColumnsLength}");

        if (!$eligibleModels) {

            $this->line("\n <options=bold,reverse;fg=red> Nothing to migrate or rollback 😳</>");

            return false;
        }

        $migrate = $this->ask('You do want to continues ? [yes|no]', 'yes');

        if ($migrate === 'yes') {
            $start = microtime(true);

            $this->migrate($eligibleModels, $eligibleModelsColumns);

            $time = number_format(microtime(true) - $start, 3);

            $this->line("Execution time {$time} seconds\n");
        }
    }

    private function migrate($models, $columns)
    {
        $this->line("Action in progress, please wait ! \n");

        foreach ($columns as $column => $modelIds) {
            $this->migrateByEligibleColumn($models, $column, $modelIds);
        }

        $this->line("<options=bold,reverse;fg=yellow> {$this->successMessage()}.  </> \n");
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
                    $params[] = $this->changeFormat($model->{$column});
                    $ids[] = $model->getKey();
                }
            }

            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);

            $query .= " CASE `{$this->model->getKeyName()}` {$cases} END WHERE `{$this->model->getKeyName()}` IN ({$ids})";

            DB::update($query, $params);
        }
    }

    abstract protected function getEligibleFields($result);

    abstract protected function changeFormat($value);

    abstract protected function successMessage();
}
