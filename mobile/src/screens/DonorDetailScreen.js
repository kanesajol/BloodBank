import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { deleteDonation, fetchDonor, recordDonation } from '../api/client';

export default function DonorDetailScreen({ route }) {
  const { donorId, donorName } = route.params;
  const [donor, setDonor] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [location, setLocation] = useState('Caraga Regional Hospital');
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  const load = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      setDonor(await fetchDonor(donorId));
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, [donorId]);

  useEffect(() => {
    load();
  }, [load]);

  if (loading) {
    return <View style={styles.center}><ActivityIndicator color="#990000" /></View>;
  }
  if (error || !donor) {
    return <View style={styles.center}><Text style={styles.error}>{error || 'Donor not found.'}</Text></View>;
  }

  return (
    <ScrollView style={styles.root} contentContainerStyle={{ padding: 16 }}>
      <Text style={styles.title}>{donorName || donor.name}</Text>
      <Text>Blood type: {donor.blood_type || '—'}</Text>
      <Text>Age: {donor.age ?? '—'}</Text>
      <Text>Address: {donor.address || '—'}</Text>
      <Text style={styles.section}>Add donation</Text>
      <TextInput style={styles.input} value={date} onChangeText={setDate} />
      <TextInput style={styles.input} value={location} onChangeText={setLocation} />
      <Pressable style={styles.button} onPress={async () => {
        setSaving(true);
        try {
          await recordDonation(donorId, { date, location: location.trim() });
          setMessage('Donation saved.');
          await load();
        } catch (e) {
          setMessage(e.message);
        } finally {
          setSaving(false);
        }
      }}>
        <Text style={styles.buttonText}>{saving ? 'Saving...' : 'Save donation'}</Text>
      </Pressable>
      {message ? <Text>{message}</Text> : null}
      {(donor.donations ?? []).map((item) => (
        <View key={item.id} style={styles.donationRow}>
          <Text>{item.date} · {item.location} · {item.bags} bag</Text>
          <Pressable
            onPress={async () => {
              try {
                await deleteDonation(item.id);
                await load();
              } catch (e) {
                setMessage(e.message);
              }
            }}
          >
            <Text style={styles.delete}>Delete</Text>
          </Pressable>
        </View>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  title: { fontSize: 20, fontWeight: '800', color: '#990000', marginBottom: 12 },
  section: { fontWeight: '700', marginVertical: 12 },
  input: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, padding: 10, marginBottom: 10 },
  button: { backgroundColor: '#990000', borderRadius: 10, padding: 12, alignItems: 'center' },
  buttonText: { color: '#fff', fontWeight: '700' },
  error: { color: '#dc2626' },
  donationRow: { marginTop: 8, paddingBottom: 8, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
  delete: { color: '#dc2626', fontWeight: '600', marginTop: 4 },
});
