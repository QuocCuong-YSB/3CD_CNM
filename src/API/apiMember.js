import axios from 'axios';

export default axios.create({
    baseURL: `http://localhost:8000/api/member`,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});
