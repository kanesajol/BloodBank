import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import * as client from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [booting, setBooting] = useState(true);

  const refreshUser = useCallback(async () => {
    const token = await client.getToken();
    if (!token) {
      setUser(null);
      return;
    }
    try {
      const profile = await client.fetchCurrentUser();
      setUser(profile);
    } catch {
      await client.setToken(null);
      setUser(null);
    }
  }, []);

  useEffect(() => {
    refreshUser().finally(() => setBooting(false));
  }, [refreshUser]);

  const signIn = useCallback(async (username, password) => {
    const data = await client.login(username, password);
    setUser(data.user);
  }, []);

  const signOut = useCallback(async () => {
    await client.logout();
    setUser(null);
  }, []);

  const value = useMemo(
    () => ({
      user,
      booting,
      signIn,
      signOut,
      refreshUser,
    }),
    [user, booting, signIn, signOut, refreshUser],
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
