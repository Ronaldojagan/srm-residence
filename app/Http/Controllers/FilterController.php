<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SavedFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FilterController extends Controller
{
    public static function applyFilter($query, $filter, $logic)
{
    $column = $filter['column'];
    $operator = $filter['operator'];
    $value = $filter['value'];

    $columnMap = [
        'user'      => 'user.name',
        'user_name'      => 'user.name',
        'payment_status'      => 'payment.payment_method',
        'user_phone'      => 'user.phone',
        'customer'  => 'user.name',
        'category'  => 'category.name',
        'owner'     => 'user.name',
        'order_id'  => 'order.order_no',
        'shop_name'           => 'shop.name',
        'payment_type'           => 'payment.payment_method',
        'shop_list_name'      => 'order.shop.name',
        'total_orders'        => 'total_orders',
        'commission_rate' => 'shop.commission_percentage',
        'owner_name' => 'shop.owner_name',
        'payment_table_status' => 'order.payment.status',
    ];

    $alternativeColumnMap = [
    ];

    if (array_key_exists($column, $columnMap)) {
        $column = $columnMap[$column];
    }

    elseif (array_key_exists($column, $alternativeColumnMap)) {
        $column = $alternativeColumnMap[$column];
    }

    if (strpos($column, '.') !== false) {
        return self::applyRelationshipFilter($query, $column, $operator, $value, $logic);
    }

    switch ($column) {
        case 'status':
            return self::applyStatusFilter($query, $operator, $value, $logic);
        case 'order_status':
            return self::applyOrderStatusFilter($query, $operator, $value, $logic);
        case 'payment_status':
            return self::applyPaymentStatusFilter($query, $operator, $value, $logic);
        case 'date':
        case 'created_at':
        case 'updated_at':
        case 'deleted_at':
        case 'start_date':
        case 'end_date':
        case 'due_date':
            return self::applyDateFilter($query, $column, $operator, $value, $logic);

        case 'price':
        case 'amount':
        case 'total':
        case 'quantity':
        case 'discount':
            return self::applyNumericFilter($query, $column, $operator, $value, $logic);
        case 'total_orders':
            return self::applyTotalOrderFilter($query, $column, $operator, $value, $logic);
        case 'is_active':
        case 'is_verified':
        case 'is_featured':
            return self::applyBooleanFilter($query, $column, $operator, $value, $logic);
        case 'payment_table_status':
            return self::applyPaymentTableStatusFilter($query, $column, $operator, $value, $logic);
        default:
            if (self::isEnumField($query, $column)) {
                return self::applyEnumFilter($query, $column, $operator, $value, $logic);
            }

            return self::applyStandardFilter($query, $column, $operator, $value, $logic);
    }
}

public static function applyTotalOrderFilter($query, $column, $operator, $value, $logic)
{
    if (in_array($column, ['total_orders', 'total_amount', 'admin_commission', 'delivery_fee'])) {
        return self::applyHavingFilter($query, $column, $operator, $value, $logic);
    }

    $method = $logic === 'and' ? 'where' : 'orWhere';

    switch ($operator) {
        case 'equals':
            $query->{$method}($column, '=', (float)$value);
            break;
        case 'not_equals':
            $query->{$method}($column, '!=', (float)$value);
            break;
        case 'greater_than':
            $query->{$method}($column, '>', (float)$value);
            break;
        case 'less_than':
            $query->{$method}($column, '<', (float)$value);
            break;
        case 'greater_than_equal':
            $query->{$method}($column, '>=', (float)$value);
            break;
        case 'less_than_equal':
            $query->{$method}($column, '<=', (float)$value);
            break;
        case 'between':
            $values = explode(',', $value);
            if (count($values) === 2) {
                $query->{$method . 'Between'}($column, [(float)$values[0], (float)$values[1]]);
            }
            break;
        default:
            $query->{$method}($column, '=', (float)$value);
    }
}

public static function applyHavingFilter($query, $column, $operator, $value, $logic)
{
    $method = $logic === 'and' ? 'having' : 'orHaving';

    switch ($operator) {
        case 'equals':
            $query->{$method}($column, '=', (float)$value);
            break;
        case 'not_equals':
            $query->{$method}($column, '!=', (float)$value);
            break;
        case 'greater_than':
            $query->{$method}($column, '>', (float)$value);
            break;
        case 'less_than':
            $query->{$method}($column, '<', (float)$value);
            break;
        case 'greater_than_equal':
            $query->{$method}($column, '>=', (float)$value);
            break;
        case 'less_than_equal':
            $query->{$method}($column, '<=', (float)$value);
            break;
        case 'between':
            $values = explode(',', $value);
            if (count($values) === 2) {
                $query->{$method}($column, '>=', (float)$values[0])
                      ->{$method}($column, '<=', (float)$values[1]);
            }
            break;
        default:
            $query->{$method}($column, '=', (float)$value);
    }
}


public static function applyOrderFilter($query, $filter, $logic)
{
    $column = $filter['column'];
    $operator = $filter['operator'];
    $value = $filter['value'];

    $columnMap = [
        'user'      => 'user.name',
        'user_name'      => 'user.name',
        'user_phone'      => 'user.phone',
        'customer'  => 'user.name',
        'category'  => 'category.name',
        'owner'     => 'user.name',
        'order_id'  => 'order_no',
        'shop_name'           => 'name',
        'product_name' => 'name',
        'price' => 'amount',
        'location' => 'address',
        'suspension' => 'suspention_status',
    ];

    if (array_key_exists($column, $columnMap)) {
        $column = $columnMap[$column];
    }

    if (strpos($column, '.') !== false) {
        return self::applyRelationshipFilter($query, $column, $operator, $value, $logic);
    }

    switch ($column) {
        case 'status':
        case 'order_status':
            return self::applyStatusFilter($query, $operator, $value, $logic);

        case 'date':
        case 'created_at':
        case 'updated_at':
        case 'deleted_at':
        case 'start_date':
        case 'end_date':
        case 'due_date':
            return self::applyDateFilter($query, $column, $operator, $value, $logic);

        case 'price':
        case 'amount':
        case 'total':
        case 'quantity':
        case 'discount':
            return self::applyNumericFilter($query, $column, $operator, $value, $logic);

        case 'is_active':
        case 'is_verified':
        case 'is_featured':
            return self::applyBooleanFilter($query, $column, $operator, $value, $logic);

        default:
            if (self::isEnumField($query, $column)) {
                return self::applyEnumFilter($query, $column, $operator, $value, $logic);
            }

            return self::applyStandardFilter($query, $column, $operator, $value, $logic);
    }
}

protected static function applyStatusFilter($query, $operator, $value, $logic)
{
    $statusValues = is_array($value) ? $value : [$value];
    $statusValues = array_map('intval', $statusValues);

    $whereMethod = $logic === 'or' ? 'orWhereIn' : 'whereIn';
    $whereNotMethod = $logic === 'or' ? 'orWhereNotIn' : 'whereNotIn';

    switch ($operator) {
        case 'equals':
            $query->$whereMethod('status', $statusValues);
            break;
        case 'not_equals':
            $query->$whereNotMethod('status', $statusValues);
            break;
        case 'is_empty':
            $query->where(function($q) use ($logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) {
                    $sub->whereNull('status')->orWhere('status', '');
                });
            });
            break;
        case 'is_not_empty':
            $query->where(function($q) use ($logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) {
                    $sub->whereNotNull('status')->where('status', '!=', '');
                });
            });
            break;
    }
}

protected static function applyPaymentTableStatusFilter($query, $operator, $value, $logic)
{
    $whereMethod = $logic === 'or' ? 'orWhere' : 'where';
    $whereNotMethod = $logic === 'or' ? 'orWhere' : 'whereNot';

    // Handle both string and array values
    $statusValues = is_array($value) ? $value : [$value];

    // Map string status to what's stored in database
    $statusMap = [
        'Paid' => 'Paid',
        'Pending' => 'Pending',
        'Refund' => 'Refund'
    ];

    $mappedValues = [];
    foreach ($statusValues as $status) {
        if (isset($statusMap[$status])) {
            $mappedValues[] = $statusMap[$status];
        } else {
            $mappedValues[] = $status; // fallback
        }
    }

    switch ($operator) {
        case 'equals':
            $query->$whereMethod(function($q) use ($mappedValues) {
                $q->whereIn('status', $mappedValues);
            });
            break;
        case 'not_equals':
            $query->$whereMethod(function($q) use ($mappedValues) {
                $q->whereNotIn('status', $mappedValues);
            });
            break;
        case 'contains':
            $query->$whereMethod(function($q) use ($mappedValues) {
                foreach ($mappedValues as $status) {
                    $q->orWhere('status', 'like', "%{$status}%");
                }
            });
            break;
        case 'is_empty':
            $query->$whereMethod(function($q) {
                $q->whereNull('status')->orWhere('status', '');
            });
            break;
        case 'is_not_empty':
            $query->$whereMethod(function($q) {
                $q->whereNotNull('status')->where('status', '!=', '');
            });
            break;
    }
}

protected static function applyOrderStatusFilter($query, $operator, $value, $logic)
{
    $statusValues = is_array($value) ? $value : [$value];

    $whereMethod = $logic === 'or' ? 'orWhereIn' : 'whereIn';
    $whereNotMethod = $logic === 'or' ? 'orWhereNotIn' : 'whereNotIn';

    switch ($operator) {
        case 'equals':
            $query->$whereMethod('status', $statusValues);
            break;

        case 'not_equals':
            $query->$whereNotMethod('status', $statusValues);
            break;

        case 'is_empty':
            $query->where(function($q) use ($logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) {
                    $sub->whereNull('status')->orWhere('status', '');
                });
            });
            break;

        case 'is_not_empty':
            $query->where(function($q) use ($logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) {
                    $sub->whereNotNull('status')->where('status', '!=', '');
                });
            });
            break;
    }

    return $query;
}

protected static function applyPaymentStatusFilter($query, $operator, $value, $logic)
{
    $statusValues = is_array($value) ? $value : [$value];

    $whereMethod = $logic === 'or' ? 'orWhereIn' : 'whereIn';
    $whereNotMethod = $logic === 'or' ? 'orWhereNotIn' : 'whereNotIn';

    switch ($operator) {
        case 'equals':
            $query->$whereMethod('status', $statusValues);
            break;

        case 'not_equals':
            $query->$whereNotMethod('status', $statusValues);
            break;

        case 'is_empty':
            $query->where(function($q) use ($logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) {
                    $sub->whereNull('status')->orWhere('status', '');
                });
            });
            break;

        case 'is_not_empty':
            $query->where(function($q) use ($logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) {
                    $sub->whereNotNull('status')->where('status', '!=', '');
                });
            });
            break;
    }

    return $query;
}

protected static function applyDateFilter($query, $column, $operator, $value, $logic)
{
    $whereMethod = $logic === 'or' ? 'orWhere' : 'where';
    $whereDateMethod = $logic === 'or' ? 'orWhereDate' : 'whereDate';
    $whereBetweenMethod = $logic === 'or' ? 'orWhereBetween' : 'whereBetween';

    switch ($operator) {
        case 'equals':
            $query->$whereDateMethod($column, '=', $value);
            break;
        case 'not_equals':
            $query->$whereDateMethod($column, '!=', $value);
            break;
        case 'greater_than':
            $query->$whereMethod($column, '>', $value);
            break;
        case 'less_than':
            $query->$whereMethod($column, '<', $value);
            break;
        case 'between':
            if (is_array($value)) {
                $query->$whereBetweenMethod($column, [$value['start'], $value['end']]);
            }
            break;
        case 'is_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNull($column);
                });
            });
            break;
        case 'is_not_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNotNull($column);
                });
            });
            break;
    }
}

protected static function applyNumericFilter($query, $column, $operator, $value, $logic)
{
    $whereMethod = $logic === 'or' ? 'orWhere' : 'where';
    $whereBetweenMethod = $logic === 'or' ? 'orWhereBetween' : 'whereBetween';

    switch ($operator) {
        case 'equals':
            $query->$whereMethod($column, '=', $value);
            break;
        case 'not_equals':
            $query->$whereMethod($column, '!=', $value);
            break;
        case 'greater_than':
            $query->$whereMethod($column, '>', $value);
            break;
        case 'less_than':
            $query->$whereMethod($column, '<', $value);
            break;
        case 'between':
            if (is_array($value)) {
                $query->$whereBetweenMethod($column, [$value['start'], $value['end']]);
            }
            break;
        case 'is_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNull($column);
                });
            });
            break;
        case 'is_not_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNotNull($column);
                });
            });
            break;
    }
}

protected static function applyBooleanFilter($query, $column, $operator, $value, $logic)
{
    $whereMethod = $logic === 'or' ? 'orWhere' : 'where';
    $booleanValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);

    switch ($operator) {
        case 'equals':
            $query->$whereMethod($column, $booleanValue);
            break;
        case 'not_equals':
            $query->$whereMethod($column, '!=', $booleanValue);
            break;
        case 'is_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNull($column);
                });
            });
            break;
        case 'is_not_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNotNull($column);
                });
            });
            break;
    }
}

protected static function applyEnumFilter($query, $column, $operator, $value, $logic)
{
    $whereMethod = $logic === 'or' ? 'orWhere' : 'where';
    $whereInMethod = $logic === 'or' ? 'orWhereIn' : 'whereIn';
    $whereNotInMethod = $logic === 'or' ? 'orWhereNotIn' : 'whereNotIn';

    $values = is_array($value) ? $value : [$value];

    switch ($operator) {
        case 'equals':
            $query->$whereInMethod($column, $values);
            break;
        case 'not_equals':
            $query->$whereNotInMethod($column, $values);
            break;
        case 'contains':
            $query->where(function($q) use ($column, $values, $whereMethod) {
                foreach ($values as $val) {
                    $q->$whereMethod($column, 'like', "%{$val}%");
                }
            });
            break;
        case 'is_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNull($column)->orWhere($column, '');
                });
            });
            break;
        case 'is_not_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNotNull($column)->where($column, '!=', '');
                });
            });
            break;
    }
}

protected static function applyRelationshipFilter($query, $column, $operator, $value, $logic)
{
    $relationParts = explode('.', $column);
    $field = array_pop($relationParts);
    $relation = implode('.', $relationParts);

    $whereHasMethod = $logic === 'or' ? 'orWhereHas' : 'whereHas';

    $query->$whereHasMethod($relation, function($q) use ($field, $operator, $value) {
        switch ($operator) {
            case 'equals':
                $q->where($field, '=', $value);
                break;
            case 'not_equals':
                $q->where($field, '!=', $value);
                break;
            case 'contains':
                $q->where($field, 'like', "%{$value}%");
                break;
            case 'starts_with':
                $q->where($field, 'like', "{$value}%");
                break;
            case 'ends_with':
                $q->where($field, 'like', "%{$value}");
                break;
            case 'greater_than':
                $q->where($field, '>', $value);
                break;
            case 'less_than':
                $q->where($field, '<', $value);
                break;
            case 'between':
                if (is_array($value)) {
                    $q->whereBetween($field, [$value['start'], $value['end']]);
                }
                break;
            case 'is_empty':
                $q->whereNull($field)->orWhere($field, '');
                break;
            case 'is_not_empty':
                $q->whereNotNull($field)->where($field, '!=', '');
                break;
        }
    });
}

protected static function applyStandardFilter($query, $column, $operator, $value, $logic)
{
    $whereMethod = $logic === 'or' ? 'orWhere' : 'where';
    $whereInMethod = $logic === 'or' ? 'orWhereIn' : 'whereIn';
    $whereNotInMethod = $logic === 'or' ? 'orWhereNotIn' : 'whereNotIn';
    $whereBetweenMethod = $logic === 'or' ? 'orWhereBetween' : 'whereBetween';

    switch ($operator) {
        case 'equals':
            if (is_array($value)) {
                $query->$whereInMethod($column, $value);
            } else {
                $query->$whereMethod($column, '=', $value);
            }
            break;
        case 'not_equals':
            if (is_array($value)) {
                $query->$whereNotInMethod($column, $value);
            } else {
                $query->$whereMethod($column, '!=', $value);
            }
            break;
        case 'contains':
            $query->$whereMethod($column, 'like', "%{$value}%");
            break;
        case 'starts_with':
            $query->$whereMethod($column, 'like', "{$value}%");
            break;
        case 'ends_with':
            $query->$whereMethod($column, 'like', "%{$value}");
            break;
        case 'greater_than':
            $query->$whereMethod($column, '>', $value);
            break;
        case 'less_than':
            $query->$whereMethod($column, '<', $value);
            break;
        case 'between':
            if (is_array($value)) {
                $query->$whereBetweenMethod($column, [$value['start'], $value['end']]);
            }
            break;
        case 'is_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNull($column)->orWhere($column, '');
                });
            });
            break;
        case 'is_not_empty':
            $query->where(function($q) use ($column, $logic) {
                $method = $logic === 'or' ? 'orWhere' : 'where';
                $q->$method(function($sub) use ($column) {
                    $sub->whereNotNull($column)->where($column, '!=', '');
                });
            });
            break;
    }
}

protected static function isEnumField($query, $column)
{
    $model = $query->getModel();
    return isset($model->getCasts()[$column]) && strpos($model->getCasts()[$column], 'enum') !== false;
}

    public function index(Request $request)
    {
        $filters = SavedFilter::where('user_id', auth()->id())
            ->where('page', $request->input('page', 'files'))
            ->where('table_type', $request->input('table_type', 'regular'))
            ->get()
            ->map(function ($filter) {
                return [
                    'id' => Crypt::encrypt($filter->id),
                    'name' => $filter->name,
                    'filters' => $filter->filters,
                ];
            });

        return response()->json($filters);
    }

    public function show(SavedFilter $saveFilter)
    {
        return response()->json($saveFilter);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'page' => 'required|string|max:255',
            'table_type' => 'required|string|max:255',
            'logic' => 'required|string|in:and,or',
            'rules' => 'required|array'
        ]);

        $filter = SavedFilter::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'page' => $validated['page'],
            'table_type' => $validated['table_type'],
            'filters' => [
                'logic' => $validated['logic'],
                'rules' => $validated['rules']
            ]
        ]);

        return response()->json($filter);
    }

    public function update(Request $request, SavedFilter $saveFilter)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logic' => 'required|string|in:and,or',
            'rules' => 'required|array'
        ]);

        $saveFilter->update([
            'name' => $validated['name'],
            'filters' => [
                'logic' => $validated['logic'],
                'rules' => $validated['rules']
            ]
        ]);

        return response()->json($saveFilter);
    }

    public function destroy(SavedFilter $saveFilter)
    {
        $saveFilter->delete();
        return response()->json(['message' => 'Filter deleted']);
    }

    public function invoice($id)
    {
        $order = Order::where('id', $id)->first();

        $pdf = Pdf::loadView('backend.invoice', compact('order'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $order->order_no . '.pdf');
    }

    public function orderComplete($id)
    {
            $order = Order::with(['user', 'shippingAddress', 'orderItems.product', 'payment'])
                        ->findOrFail($id);

            $order->status = 'Completed';

            if ($order->payment) {
                $order->payment->status = 'Paid';
                $order->payment->save();
            }

            $order->save();

            return view('backend.order_completed', [
                'order' => $order,
                'currency' => '₹',
                'date_format' => 'd-m-Y H:i'
            ]);
        try {

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'Invalid order ID');
        } catch (\Exception $e) {
            abort(500, 'Error completing order: ' . $e->getMessage());
        }
    }
}
