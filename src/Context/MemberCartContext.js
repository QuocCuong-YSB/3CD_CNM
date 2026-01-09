import { createContext, useEffect, useState } from 'react';
import apiMember from '../API/apiMember';

const MemberCartContext = createContext();

export const UserProvider = ({ children }) => {
    const cartStorege = JSON.parse(localStorage.getItem('total')) || 0;
    const [cart, setCart] = useState(cartStorege);

    useEffect(() => {
        if (cart > 0) {
            localStorage.setItem('total', JSON.stringify(cart));
        } else {
            localStorage.removeItem('total');
        }
    }, [cart]);

    const resetCart = () => {
        setCart(0);
    };

    const fetchCartCount = async () => {
        try {
            const res = await apiMember.get('/cart/count');
            setCart(res.data.count ?? 0);
        } catch {
            console.error('Fetch cart count failed');
        }
    };

    return (
        <MemberCartContext.Provider
            value={{ cart, setCart, fetchCartCount, resetCart }}
        >
            {children}
        </MemberCartContext.Provider>
    );
};

export default MemberCartContext;
