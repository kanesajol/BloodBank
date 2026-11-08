import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import * as client from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [booting, setBooting] = useState(true);

  useEffect(() => {
    client.setToken(null)
      .catch((error) => {
        console.error('Unable to clear the saved sign-in on app launch.', error);
      })
      .finally(() => {
        setUser(null);
        setBooting(false);
      });
  }, []);

  const signIn = useCallback(async (username, password) => {
    const data = await client.login(username, password);
    setUser(data.user);
  }, []);

  const signUp = useCallback(async (payload) => {
    const data = await client.signup(payload);
    setUser(data.user);
  }, []);

  const signOut = useCallback(async () => {
    setUser(null);
    await client.logout();
  }, []);

  const applyUserUpdate = useCallback((profile) => {
    setUser(profile);
  }, []);

  const value = useMemo(
    () => ({
      user,
      booting,
      signIn,
      signUp,
      signOut,
      applyUserUpdate,
    }),
    [user, booting, signIn, signUp, signOut, applyUserUpdate],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return ctx;
}
