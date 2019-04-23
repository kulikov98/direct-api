CREATE TABLE resources (
    id int PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name varchar(255),
    user_id	int REFERENCES users (id),
    yandex_direct_login varchar(255),
    yandex_direct_token varchar(255),
    yandex_direct_token_expire timestamp,
    yandex_direct_balance numeric,	
    yandex_market_login	varchar(255),
    yandex_makret_token varchar(255),
    yandex_market_token_expire date,
    yandex_market_balance numeric,
    created_at timestamp,
    updated_at timestamp
);