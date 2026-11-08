import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';
import { API_BASE_URL } from '../config';

const TOKEN_KEY = 'crh_api_token';

async function storageGet(key) {
  if (Platform.OS === 'web') {
    return globalThis.localStorage?.getItem(key) ?? null;
  }
  return SecureStore.getItemAsync(key);
}

async function storageSet(key, value) {
  if (Platform.OS === 'web') {
    if (value) {
      globalThis.localStorage?.setItem(key, value);
    } else {
      globalThis.localStorage?.removeItem(key);
    }
    return;
  }
  if (value) {
    await SecureStore.setItemAsync(key, value);
  } else {
    await SecureStore.deleteItemAsync(key);
  }
}

export async function getToken() {
  return storageGet(TOKEN_KEY);
}

export async function setToken(token) {
  await storageSet(TOKEN_KEY, token ?? null);
}

export async function apiRequest(path, options = {}) {
  const token = await getToken();
  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(options.headers ?? {}),
  };
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message =
      data?.message ??
      data?.errors?.username?.[0] ??
      Object.values(data?.errors ?? {})?.[0]?.[0] ??
      'Request failed. Check your connection and API URL.';
    throw new Error(message);
  }

  return data;
}

export async function login(username, password) {
  const data = await apiRequest('/login', {
    method: 'POST',
    body: JSON.stringify({ username, password }),
  });
  await setToken(data.token);
  return data;
}

export async function logout() {
  try {
    await apiRequest('/logout', { method: 'POST' });
  } finally {
    await setToken(null);
  }
}

export async function fetchCurrentUser() {
  const data = await apiRequest('/user');
  return data.user;
}

export async function searchDonors(query) {
  return apiRequest(`/donors/search?q=${encodeURIComponent(query)}`);
}

export async function searchReturningDonors(query) {
  return apiRequest(`/donors/returning?q=${encodeURIComponent(query)}`);
}

export async function registerDonor(payload) {
  return apiRequest('/donors', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export async function fetchDonor(id) {
  const data = await apiRequest(`/donors/${id}`);
  return data.donor;
}

export async function recordDonation(donorId, payload) {
  return apiRequest(`/donors/${donorId}/donations`, {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export async function deleteDonation(donationId) {
  return apiRequest(`/donations/${donationId}`, { method: 'DELETE' });
}

export async function fetchConsumptionMeta() {
  return apiRequest('/consumption/meta');
}

export async function fetchConsumption(params = {}) {
  const query = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && String(value).trim() !== '') {
      query.set(key, String(value));
    }
  });
  const suffix = query.toString() ? `?${query.toString()}` : '';
  return apiRequest(`/consumption${suffix}`);
}

export async function createConsumptionRecord(payload) {
  return apiRequest('/consumption', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export async function deleteConsumptionRecord(id) {
  return apiRequest(`/consumption/${id}`, { method: 'DELETE' });
}

export async function fetchReports(params = {}) {
  const query = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && String(value).trim() !== '') {
      query.set(key, String(value));
    }
  });
  const suffix = query.toString() ? `?${query.toString()}` : '';
  return apiRequest(`/reports${suffix}`);
}

export async function fetchStaff() {
  return apiRequest('/staff');
}

export async function fetchStaffRoles() {
  return apiRequest('/staff/roles');
}

export async function fetchStaffLog(date) {
  const suffix = date ? `?date=${encodeURIComponent(date)}` : '';
  return apiRequest(`/staff/log${suffix}`);
}

export async function createStaff(payload) {
  return apiRequest('/staff', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export async function deleteStaff(userId) {
  return apiRequest(`/staff/${userId}`, { method: 'DELETE' });
}
