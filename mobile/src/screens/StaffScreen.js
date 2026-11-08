import { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { createStaff, deleteStaff, fetchStaff, fetchStaffRoles } from '../api/client';
import { useAuth } from '../context/AuthContext';

export default function StaffScreen({ navigation }) {
  const { user } = useAuth();
  const [staff, setStaff] = useState([]);
  const [roles, setRoles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [fullName, setFullName] = useState('');
  const [username, setUsername] = useState('');
  const [role, setRole] = useState('');
  const [contact, setContact] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const [staffData, rolesData] = await Promise.all([fetchStaff(), fetchStaffRoles()]);
      setStaff(staffData.staff ?? []);
      setRoles(rolesData.roles ?? []);
      setRole((current) => current || rolesData.roles?.[0] || '');
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  return (
    <View style={styles.root}>
      <Text style={styles.you}>Signed in: {user?.name} ({user?.role})</Text>
      <View style={styles.actions}>
        <Pressable onPress={() => navigation.navigate('StaffLog')}>
          <Text style={styles.link}>View staff log</Text>
        </Pressable>
        <Pressable onPress={() => setShowForm((v) => !v)}>
          <Text style={styles.link}>{showForm ? 'Cancel' : '+ Add staff'}</Text>
        </Pressable>
      </View>
      {showForm ? (
        <View style={styles.form}>
          <TextInput style={styles.input} placeholder="Full name" value={fullName} onChangeText={setFullName} />
          <TextInput style={styles.input} placeholder="Username" value={username} onChangeText={setUsername} autoCapitalize="none" />
          <TextInput style={styles.input} placeholder="Role" value={role} onChangeText={setRole} />
          <TextInput style={styles.input} placeholder="Contact" value={contact} onChangeText={setContact} />
          <Pressable
            style={styles.saveBtn}
            onPress={async () => {
              try {
                await createStaff({
                  full_name: fullName.trim(),
                  username: username.trim(),
                  role: role.trim(),
                  contact: contact.trim(),
                });
                setShowForm(false);
                setFullName('');
                setUsername('');
                setContact('');
                await load();
              } catch (e) {
                setError(e.message);
              }
            }}
          >
            <Text style={styles.saveBtnText}>Create (default password: password)</Text>
          </Pressable>
        </View>
      ) : null}
      {loading ? <ActivityIndicator color="#990000" /> : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <FlatList
        data={staff}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <Text style={styles.name}>{item.name}</Text>
            <Text>@{item.username} · {item.role}</Text>
            <Text style={styles.meta}>{item.status} · {item.contact}</Text>
            {item.id !== user?.id ? (
              <Pressable
                onPress={async () => {
                  try {
                    await deleteStaff(item.id);
                    await load();
                  } catch (e) {
                    setError(e.message);
                  }
                }}
              >
                <Text style={styles.delete}>Delete</Text>
              </Pressable>
            ) : null}
          </View>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff', padding: 16 },
  you: { fontWeight: '600', marginBottom: 8 },
  actions: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  link: { color: '#990000', fontWeight: '700' },
  form: { marginBottom: 12 },
  input: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, padding: 10, marginBottom: 8 },
  saveBtn: { backgroundColor: '#990000', borderRadius: 10, padding: 12, alignItems: 'center' },
  saveBtnText: { color: '#fff', fontWeight: '700', fontSize: 12 },
  card: { paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
  name: { fontWeight: '700', fontSize: 16 },
  meta: { color: '#64748b', marginTop: 4 },
  delete: { color: '#dc2626', marginTop: 6, fontWeight: '600' },
  error: { color: '#dc2626' },
});
