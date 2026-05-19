import { useState, useEffect } from "react";

const useFetch = (url, method = 'GET', options = {}) => {
    const [data, setData] = useState(null);

    useEffect(() => {
        const params = {};
        params.method = method;
        params.headers = options.headers || {};
        if (method.toUpperCase() === 'POST') {
            params.body = options.body || null;
        }
        if (url) {
            fetch(url, params)
                .then((res) => res.json())
                .then((data) => {
                    setData(data);
                })
                .finally(() => {
                    if (options.callback) {
                        options.callback();
                    }
                });
        }
    }, [url]);

    return [data];
};

export default useFetch;
