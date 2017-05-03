<?

namespace App\Contracts;

use App\Support\RequestOptions;

interface BaseCrudServiceContract
{
    /**
     * @param RequestOptions $options
     *
     * @return mixed
     */
    public function getAll(RequestOptions $options);

    /**
     * @param $id
     * @param RequestOptions|null $options
     *
     * @return mixed
     */
    public function getOne($id, RequestOptions $options = null);
}
